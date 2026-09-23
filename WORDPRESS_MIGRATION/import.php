<?php
/**
 * Import manifest runner. Invoked via:
 *   wp eval-file wp-content/to-wordpress/import.php wp-content/to-wordpress/import-manifest.json
 * Runs entirely inside the wp-env container. Idempotent on slug + post_type
 * and designed to NEVER crash-exit when one item fails: every per-item
 * operation is wrapped in try/catch so partial imports still produce a
 * usable site, and the caller sees a clear list of failures.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Give ourselves headroom — migrating 100+ posts with sideloaded media
// routinely exceeds PHP's default 30-second limit and 128 MB memory.
@set_time_limit( 0 );
@ini_set( 'memory_limit', '512M' );

// If anything below raises a fatal, at least say WHICH item we were on
// instead of the user seeing an opaque "Allowed memory size exhausted".
$GLOBALS['towp_current_item'] = null;
register_shutdown_function( function () {
    $err = error_get_last();
    if ( $err && in_array( $err['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ], true ) ) {
        $slug = $GLOBALS['towp_current_item']['slug'] ?? '?';
        $type = $GLOBALS['towp_current_item']['post_type'] ?? '?';
        fwrite( STDERR, "\ntowp FATAL while importing {$type} \"{$slug}\": {$err['message']} at {$err['file']}:{$err['line']}\n" );
    }
} );

// Centralized per-item exception wrapper. Keeps the loop alive so one
// bad post doesn't lose the other 99.
$towp_failures = [];
function towp_try( $label, callable $fn ) {
    global $towp_failures;
    try {
        return $fn();
    } catch ( \Throwable $e ) {
        $towp_failures[] = $label . ': ' . $e->getMessage();
        WP_CLI::warning( $label . ': ' . $e->getMessage() );
        return null;
    }
}

// ── Preset cleanup ────────────────────────────────────────────────────
// Strip WordPress's default content so the migrated site is a faithful
// replica of the source. Only touches well-known preset slugs; anything
// the user or a previous run created stays put.
towp_try( 'preset cleanup', function () {
    $preset_slugs = [ 'hello-world', 'sample-page', 'privacy-policy' ];
    foreach ( $preset_slugs as $s ) {
        foreach ( [ 'post', 'page' ] as $t ) {
            $p = get_page_by_path( $s, OBJECT, $t );
            if ( $p ) { wp_delete_post( $p->ID, true ); }
        }
    }
    $autodrafts = get_posts( [
        'post_status'    => 'auto-draft',
        'posts_per_page' => -1,
        'post_type'      => [ 'post', 'page' ],
        'fields'         => 'ids',
    ] );
    foreach ( $autodrafts as $id ) { wp_delete_post( $id, true ); }
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_author = 'A WordPress Commenter' AND comment_approved IN ('1','0')" );
} );

// ── Manifest load ─────────────────────────────────────────────────────
$manifest_rel = isset( $args[0] ) ? $args[0] : 'wp-content/to-wordpress/import-manifest.json';
$manifest_path = ABSPATH . ltrim( $manifest_rel, '/' );
if ( ! file_exists( $manifest_path ) ) {
    WP_CLI::error( 'manifest not found: ' . $manifest_path );
}
$manifest_raw = file_get_contents( $manifest_path );
$manifest = json_decode( $manifest_raw, true );
if ( ! is_array( $manifest ) ) {
    WP_CLI::error( 'invalid manifest JSON at ' . $manifest_path . ' (json_last_error=' . json_last_error_msg() . ')' );
}
if ( empty( $manifest['items'] ) || ! is_array( $manifest['items'] ) ) {
    WP_CLI::warning( 'manifest has no items to import' );
}

towp_try( 'site_title', function () use ( $manifest ) {
    if ( ! empty( $manifest['site_title'] ) ) {
        update_option( 'blogname', $manifest['site_title'] );
    }
} );
towp_try( 'permalink_structure', function () use ( $manifest ) {
    if ( ! empty( $manifest['permalink_structure'] ) ) {
        update_option( 'permalink_structure', $manifest['permalink_structure'] );
    }
} );

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// ── Media sideload ────────────────────────────────────────────────────
$media_map = [];
$work_mount = isset( $manifest['work_mount'] ) ? trim( (string) $manifest['work_mount'], '/' ) : '';
$media_rel = isset( $manifest['media_dir_relative'] ) ? trim( (string) $manifest['media_dir_relative'], '/' ) : '';
$work_root = ABSPATH . $work_mount;
$media_dir = $work_root . '/' . $media_rel;

$media_count = 0;
$media_skipped = 0;
foreach ( (array) ( $manifest['media'] ?? [] ) as $m ) {
    if ( empty( $m['basename'] ) ) { $media_skipped++; continue; }
    $basename = (string) $m['basename'];
    towp_try( 'media:' . $basename, function () use ( $m, $media_dir, &$media_map, &$media_count, $basename ) {
        $source_file = $media_dir . '/' . $basename;
        if ( ! file_exists( $source_file ) ) {
            WP_CLI::warning( 'missing media: ' . $source_file );
            return;
        }
        $existing = get_posts( [
            'post_type'      => 'attachment',
            'meta_key'       => '_towp_media_basename',
            'meta_value'     => $basename,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ] );
        if ( ! empty( $existing ) ) {
            $media_map[ $basename ] = (int) $existing[0];
            return;
        }
        $tmp = wp_tempnam( $basename );
        if ( ! $tmp || ! @copy( $source_file, $tmp ) ) {
            @unlink( $tmp );
            WP_CLI::warning( 'failed to stage media: ' . $basename );
            return;
        }
        $attachment_id = media_handle_sideload( [ 'name' => $basename, 'tmp_name' => $tmp ], 0 );
        if ( is_wp_error( $attachment_id ) ) {
            @unlink( $tmp );
            WP_CLI::warning( 'failed media: ' . $basename . ': ' . $attachment_id->get_error_message() );
            return;
        }
        update_post_meta( $attachment_id, '_towp_media_basename', $basename );
        $media_map[ $basename ] = (int) $attachment_id;
        $media_count++;
    } );
}
WP_CLI::log( 'media: ' . $media_count . ' imported, ' . $media_skipped . ' skipped, ' . count( $media_map ) . ' total mapped' );

$user_map = [];
function towp_ensure_user( $login, $email ) {
    if ( ! $login ) { return 0; }
    $user = get_user_by( 'login', $login );
    if ( $user ) { return (int) $user->ID; }
    $email = $email ?: ( $login . '@localhost.invalid' );
    $pw = wp_generate_password( 24, false );
    $uid = wp_insert_user( [
        'user_login'    => $login,
        'user_email'    => $email,
        'user_pass'     => $pw,
        'role'          => 'author',
        'display_name'  => $login,
    ] );
    return is_wp_error( $uid ) ? 0 : (int) $uid;
}

function towp_ensure_terms( $names, $taxonomy ) {
    $ids = [];
    foreach ( $names as $name ) {
        $name = trim( (string) $name );
        if ( ! $name ) continue;
        $existing = term_exists( $name, $taxonomy );
        if ( $existing ) {
            $ids[] = (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
        } else {
            $new = wp_insert_term( $name, $taxonomy );
            if ( ! is_wp_error( $new ) ) { $ids[] = (int) $new['term_id']; }
        }
    }
    return $ids;
}

function towp_rewrite_media_refs( $content, $media_map ) {
    return preg_replace_callback(
        '/media:([^)\s"\']+)/',
        function ( $m ) use ( $media_map ) {
            $key = $m[1];
            if ( isset( $media_map[ $key ] ) ) {
                $url = wp_get_attachment_url( $media_map[ $key ] );
                return $url ?: $m[0];
            }
            return $m[0];
        },
        $content
    );
}

// ── Items loop ────────────────────────────────────────────────────────
$created = 0;
$updated = 0;
$skipped = 0;
$registered_types = get_post_types( [], 'names' );
foreach ( (array) ( $manifest['items'] ?? [] ) as $item_idx => $item ) {
    if ( ! is_array( $item ) ) { $skipped++; continue; }
    $slug_raw = isset( $item['slug'] ) ? (string) $item['slug'] : '';
    $slug = sanitize_title( $slug_raw );
    if ( ! $slug ) {
        $slug = 'imported-' . $item_idx;
    }
    $post_type = isset( $item['post_type'] ) && $item['post_type'] ? (string) $item['post_type'] : 'post';
    // If the manifest references a CPT that was never registered (e.g.
    // the plugin failed to activate), fall back to 'post' instead of
    // letting wp_insert_post reject the whole row.
    if ( ! in_array( $post_type, $registered_types, true ) ) {
        WP_CLI::warning( 'unknown post_type "' . $post_type . '" for "' . $slug . '" — falling back to "post"' );
        $post_type = 'post';
    }

    $GLOBALS['towp_current_item'] = [ 'slug' => $slug, 'post_type' => $post_type ];

    $result = towp_try( 'item:' . $post_type . ':' . $slug, function () use ( $item, $slug, $post_type, $media_map, &$created, &$updated ) {
        $existing = get_page_by_path( $slug, OBJECT, $post_type );
        $content = towp_rewrite_media_refs( isset( $item['content'] ) ? (string) $item['content'] : '', $media_map );
        $author_id = isset( $item['author_login'] ) && $item['author_login']
            ? towp_ensure_user( (string) $item['author_login'], isset( $item['author_email'] ) ? (string) $item['author_email'] : '' )
            : 0;
        $date_str = isset( $item['date'] ) ? (string) $item['date'] : '';
        $ts = $date_str ? strtotime( $date_str ) : false;
        if ( ! $ts ) { $ts = time(); }
        $status = isset( $item['status'] ) && $item['status'] ? (string) $item['status'] : 'publish';
        // WordPress only accepts a known-good status string.
        $allowed_statuses = [ 'publish', 'draft', 'pending', 'private', 'future', 'trash' ];
        if ( ! in_array( $status, $allowed_statuses, true ) ) { $status = 'draft'; }

        $data = [
            'post_type'    => $post_type,
            'post_title'   => isset( $item['title'] ) ? (string) $item['title'] : $slug,
            'post_name'    => $slug,
            'post_status'  => $status,
            'post_date'    => gmdate( 'Y-m-d H:i:s', $ts ),
            'post_date_gmt'=> gmdate( 'Y-m-d H:i:s', $ts ),
            'post_content' => $content,
            'post_excerpt' => isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '',
            'post_author'  => $author_id ?: 1,
        ];
        if ( $existing ) {
            $data['ID'] = $existing->ID;
            $post_id = wp_update_post( wp_slash( $data ), true );
            if ( is_wp_error( $post_id ) ) { throw new \RuntimeException( $post_id->get_error_message() ); }
            $updated++;
        } else {
            $post_id = wp_insert_post( wp_slash( $data ), true );
            if ( is_wp_error( $post_id ) ) { throw new \RuntimeException( $post_id->get_error_message() ); }
            $created++;
        }
        // Terms: only set when the taxonomy is actually registered for
        // this post type — otherwise wp_set_object_terms will warn.
        if ( ! empty( $item['categories'] ) && taxonomy_exists( 'category' ) ) {
            $cat_ids = towp_ensure_terms( (array) $item['categories'], 'category' );
            if ( $cat_ids ) { wp_set_object_terms( $post_id, $cat_ids, 'category', false ); }
        }
        if ( ! empty( $item['tags'] ) && taxonomy_exists( 'post_tag' ) ) {
            wp_set_object_terms( $post_id, (array) $item['tags'], 'post_tag', false );
        }
        if ( ! empty( $item['meta'] ) && is_array( $item['meta'] ) ) {
            foreach ( $item['meta'] as $k => $v ) {
                // WP meta keys must be scalar strings; skip anything weird.
                if ( ! is_string( $k ) || $k === '' ) continue;
                update_post_meta( $post_id, $k, $v );
            }
        }
        if ( ! empty( $item['featured_image_rel'] ) ) {
            $rel = (string) $item['featured_image_rel'];
            $base = basename( $rel );
            $flat = str_replace( '/', '__', ltrim( $rel, '/' ) );
            if ( isset( $media_map[ $flat ] ) ) {
                set_post_thumbnail( $post_id, $media_map[ $flat ] );
            } elseif ( isset( $media_map[ $base ] ) ) {
                set_post_thumbnail( $post_id, $media_map[ $base ] );
            }
        }
        return $post_id;
    } );
    if ( $result === null ) { $skipped++; }
    $GLOBALS['towp_current_item'] = null;
}
WP_CLI::log( 'items: ' . $created . ' created, ' . $updated . ' updated, ' . $skipped . ' skipped' );

// ── Front page / blog / privacy settings ──────────────────────────────
towp_try( 'front_page', function () use ( $manifest ) {
    if ( empty( $manifest['front_page_slug'] ) ) { return; }
    $home = get_page_by_path( sanitize_title( $manifest['front_page_slug'] ), OBJECT, 'page' );
    if ( $home ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', (int) $home->ID );
        WP_CLI::log( 'front page: ' . $home->post_title . ' (#' . $home->ID . ')' );
    }
} );

towp_try( 'blog_index', function () use ( $manifest ) {
    if ( ! empty( $manifest['blog_index_page_slug'] ) ) {
        $blog_slug = sanitize_title( $manifest['blog_index_page_slug'] );
        $blog = get_page_by_path( $blog_slug, OBJECT, 'page' );
        if ( ! $blog ) {
            $blog_id = wp_insert_post( [
                'post_type'    => 'page',
                'post_title'   => ucwords( str_replace( '-', ' ', $blog_slug ) ),
                'post_name'    => $blog_slug,
                'post_status'  => 'publish',
                'post_content' => '',
                'post_author'  => 1,
            ], true );
            if ( ! is_wp_error( $blog_id ) ) {
                $blog = get_post( (int) $blog_id );
                WP_CLI::log( 'blog index page created: ' . $blog_slug . ' (#' . (int) $blog_id . ')' );
            }
        }
        if ( $blog ) {
            update_option( 'page_for_posts', (int) $blog->ID );
            WP_CLI::log( 'blog index: ' . $blog->post_title . ' (#' . $blog->ID . ')' );
        }
    } elseif ( empty( $manifest['front_page_slug'] ) ) {
        update_option( 'page_for_posts', 0 );
    }
} );

towp_try( 'privacy_page', function () use ( $manifest ) {
    if ( empty( $manifest['privacy_page_slug'] ) ) { return; }
    $priv = get_page_by_path( sanitize_title( $manifest['privacy_page_slug'] ), OBJECT, 'page' );
    if ( $priv ) {
        update_option( 'wp_page_for_privacy_policy', (int) $priv->ID );
        WP_CLI::log( 'privacy page: ' . $priv->post_title . ' (#' . $priv->ID . ')' );
    }
} );

// ── Page templates ────────────────────────────────────────────────────
towp_try( 'page_templates', function () use ( $manifest ) {
    $theme_dir = get_stylesheet_directory();
    foreach ( (array) ( $manifest['items'] ?? [] ) as $it ) {
        if ( empty( $it['layout'] ) || ( $it['post_type'] ?? '' ) !== 'page' ) { continue; }
        $slug = sanitize_title( $it['slug'] ?? '' );
        if ( ! $slug ) { continue; }
        $page = get_page_by_path( $slug, OBJECT, 'page' );
        if ( ! $page ) { continue; }
        $layout = sanitize_title( $it['layout'] );
        $candidates = [ 'page-' . $layout . '.php', 'page-templates/' . $layout . '.php', 'template-' . $layout . '.php' ];
        $chosen = 'default';
        foreach ( $candidates as $c ) {
            if ( file_exists( $theme_dir . '/' . $c ) ) { $chosen = $c; break; }
        }
        if ( $chosen !== 'default' ) {
            update_post_meta( $page->ID, '_wp_page_template', $chosen );
        }
    }
} );

// ── Primary nav menu ──────────────────────────────────────────────────
towp_try( 'primary_menu', function () use ( $manifest ) {
    if ( empty( $manifest['menu_items'] ) || ! is_array( $manifest['menu_items'] ) ) { return; }
    $menu_name = 'Primary';
    $menu = wp_get_nav_menu_object( $menu_name );
    if ( ! $menu ) {
        $menu_id = wp_create_nav_menu( $menu_name );
    } else {
        $menu_id = (int) $menu->term_id;
        $existing = wp_get_nav_menu_items( $menu_id );
        if ( is_array( $existing ) ) {
            foreach ( $existing as $mi ) { wp_delete_post( $mi->ID, true ); }
        }
    }
    if ( is_wp_error( $menu_id ) ) { return; }

    $add = function ( $items, $parent_id ) use ( $menu_id, &$add ) {
        foreach ( (array) $items as $item ) {
            if ( ! is_array( $item ) ) { continue; }
            $title = isset( $item['title'] ) ? (string) $item['title'] : '';
            $url = isset( $item['url'] ) ? (string) $item['url'] : '';
            if ( ! $title || ! $url ) { continue; }
            // Resolve URL smartly: external/absolute links pass through
            // verbatim, site-relative paths get home_url() applied. Use
            // ~ delimiters so '#' inside the pattern doesn't collide.
            if ( preg_match( '~^(https?:|mailto:|tel:|sms:|ftp:|#|//)~i', $url ) ) {
                $resolved_url = $url;
            } else {
                $resolved_url = home_url( '/' . ltrim( $url, '/' ) );
            }
            $new_id = wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-title'     => $title,
                'menu-item-url'       => $resolved_url,
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => (int) $parent_id,
            ] );
            if ( ! is_wp_error( $new_id ) && ! empty( $item['children'] ) && is_array( $item['children'] ) ) {
                $add( $item['children'], (int) $new_id );
            }
        }
    };
    $add( $manifest['menu_items'], 0 );

    $locations = get_theme_mod( 'nav_menu_locations' );
    if ( ! is_array( $locations ) ) { $locations = []; }
    $locations['primary'] = (int) $menu_id;
    set_theme_mod( 'nav_menu_locations', $locations );
    WP_CLI::log( 'primary menu populated with ' . count( $manifest['menu_items'] ) . ' top-level items' );
} );

towp_try( 'flush_rewrite_rules', function () { flush_rewrite_rules( false ); } );

if ( ! empty( $towp_failures ) ) {
    WP_CLI::warning( sprintf( 'import completed with %d non-fatal failures', count( $towp_failures ) ) );
    foreach ( array_slice( $towp_failures, 0, 20 ) as $f ) {
        WP_CLI::log( '  - ' . $f );
    }
    if ( count( $towp_failures ) > 20 ) {
        WP_CLI::log( '  … ' . ( count( $towp_failures ) - 20 ) . ' more' );
    }
}

WP_CLI::success( sprintf(
    'import: %d created, %d updated, %d skipped, %d media',
    $created, $updated, $skipped, count( $media_map )
) );
