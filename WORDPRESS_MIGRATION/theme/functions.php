<?php
if (!defined('ABSPATH')) { exit; }
add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['caption','comment-form','comment-list','gallery','search-form','script','style']);
  register_nav_menus(['primary' => __('Primary', 'ryangoldbacher'), 'footer' => __('Footer', 'ryangoldbacher')]);
});
add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('ryangoldbacher', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
});
