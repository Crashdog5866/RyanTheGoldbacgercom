import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://ryangoldbacher.com',
  integrations: [sitemap()],
  vite: {
    preview: {
      allowedHosts: true,
    },
  },
});
