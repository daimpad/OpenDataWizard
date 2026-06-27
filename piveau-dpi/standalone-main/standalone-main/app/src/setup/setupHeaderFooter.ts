import PiveauHeaderFooter from '@open-data-bayern/piveau-header-footer';
import { Header, Footer } from '@open-data-bayern/piveau-header-footer';
import { App } from 'vue';

import '@open-data-bayern/piveau-header-footer/dist/piveau-header-footer.css';
export function setupHeaderFooter(app: App) {
  app.use(PiveauHeaderFooter);
  app.component('piveau-header', Header);
  app.component('piveau-footer', Footer);
}
