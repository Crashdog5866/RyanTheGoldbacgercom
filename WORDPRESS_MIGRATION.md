# WordPress Migration

Generated and maintained by `to-wordpress`.

- Source: `/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher`
- Created: 2026-09-22T06:32:18.098Z
- Updated: 2026-09-22T06:33:14.570Z


## Phases

| phase | status | finished | notes |
|---|---|---|---|
| `detect` | ok | 2026-09-22T06:32:18.137Z |  |
| `plan` | ok | 2026-09-22T06:32:19.074Z |  |
| `boot` | ok | 2026-09-22T06:33:02.504Z |  |
| `theme` | ok | 2026-09-22T06:33:03.222Z |  |
| `normalize` | ok | 2026-09-22T06:33:03.617Z |  |
| `plugin` | ok | 2026-09-22T06:33:05.708Z |  |
| `import` | ok | 2026-09-22T06:33:14.570Z |  |
| `verify` | pending |  |  |
| `fix` | pending |  |  |
| `testfix` | pending |  |  |
| `tune` | pending |  |  |

## Status

finished import at 2026-09-22T06:33:14.570Z


<!-- TOWP:STATE:START
{
  "version": 1,
  "sourceDir": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher",
  "createdAt": "2026-09-22T06:32:18.098Z",
  "updatedAt": "2026-09-22T06:33:14.570Z",
  "phases": {
    "detect": {
      "status": "ok",
      "startedAt": "2026-09-22T06:32:18.107Z",
      "finishedAt": "2026-09-22T06:32:18.137Z"
    },
    "plan": {
      "status": "ok",
      "startedAt": "2026-09-22T06:32:19.048Z",
      "finishedAt": "2026-09-22T06:32:19.074Z"
    },
    "boot": {
      "status": "ok",
      "startedAt": "2026-09-22T06:32:19.521Z",
      "finishedAt": "2026-09-22T06:33:02.504Z"
    },
    "theme": {
      "status": "ok",
      "startedAt": "2026-09-22T06:33:03.126Z",
      "finishedAt": "2026-09-22T06:33:03.222Z"
    },
    "normalize": {
      "status": "ok",
      "startedAt": "2026-09-22T06:33:03.593Z",
      "finishedAt": "2026-09-22T06:33:03.617Z"
    },
    "plugin": {
      "status": "ok",
      "startedAt": "2026-09-22T06:33:03.975Z",
      "finishedAt": "2026-09-22T06:33:05.708Z"
    },
    "import": {
      "status": "ok",
      "startedAt": "2026-09-22T06:33:06.109Z",
      "finishedAt": "2026-09-22T06:33:14.570Z"
    },
    "verify": {
      "status": "pending"
    },
    "fix": {
      "status": "pending"
    },
    "testfix": {
      "status": "pending"
    },
    "tune": {
      "status": "pending"
    }
  },
  "notes": [],
  "sections": [
    {
      "heading": "Status",
      "body": "finished import at 2026-09-22T06:33:14.570Z"
    }
  ],
  "detected": {
    "sourceDir": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher",
    "kind": "astro",
    "themeSlug": "ryangoldbacher",
    "pluginSlug": "ryangoldbacher-site",
    "layouts": [
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/about.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/contact.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/index.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/podcasts.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/resume.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/tours.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog/2026-09-20-digico-console-opinion.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog/2026-09-20-technical-philosophy-learn-by-doing.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/layouts/BaseLayout.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/components/Analytics.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/components/FAQ.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/components/Header.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/components/SeoHead.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/components/TourDates.astro"
    ],
    "includes": [],
    "sassFiles": [],
    "assetsDirs": [
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/public"
    ],
    "dataFiles": [],
    "ssgPlugins": [],
    "collections": [],
    "pages": [
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/about.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/contact.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/index.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/podcasts.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/resume.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/tours.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog/2026-09-20-digico-console-opinion.astro",
      "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog/2026-09-20-technical-philosophy-learn-by-doing.astro"
    ],
    "frontMatterKeys": [],
    "features": {},
    "detectorBriefing": "Source is an Astro site. Content collections under src/content/ each map to a WordPress collection; files under src/pages/ map to WordPress pages. .astro files are React-like component templates — read their HTML and inline styles for theme fidelity; ignore their imports/scripts."
  },
  "choices": {
    "keepPermalinks": true,
    "customPostTypes": [],
    "createRedirects": true,
    "blogIndexPageSlug": "blog",
    "pages": [
      {
        "sourcePath": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog.astro",
        "slug": "blog",
        "title": "Blog",
        "role": "blog-index"
      },
      {
        "sourcePath": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/contact.astro",
        "slug": "contact",
        "title": "Contact",
        "role": "contact"
      },
      {
        "sourcePath": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/podcasts.astro",
        "slug": "podcasts",
        "title": "Podcasts",
        "role": "none"
      },
      {
        "sourcePath": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/resume.astro",
        "slug": "resume",
        "title": "Resume",
        "role": "none"
      },
      {
        "sourcePath": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/tours.astro",
        "slug": "tours",
        "title": "Tours",
        "role": "none"
      },
      {
        "sourcePath": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog/2026-09-20-digico-console-opinion.astro",
        "slug": "2026-09-20-digico-console-opinion",
        "title": "2026 09 20 Digico Console Opinion",
        "role": "none"
      },
      {
        "sourcePath": "/Users/m1mac/.openclaw/workspace/algorithm-tickler/ryangoldbacher/src/pages/blog/2026-09-20-technical-philosophy-learn-by-doing.astro",
        "slug": "2026-09-20-technical-philosophy-learn-by-doing",
        "title": "2026 09 20 Technical Philosophy Learn By Doing",
        "role": "none"
      }
    ],
    "adminUser": "admin",
    "adminPassword": "password",
    "adminEmail": "admin@example.com"
  }
}
TOWP:STATE:END -->
