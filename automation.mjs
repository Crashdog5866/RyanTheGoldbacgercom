import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://ryanthegoldbacher.com',
  integrations: [sitemap()],
  vite: {
    preview: { allowedHosts: true },
  },
});
