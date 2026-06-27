import { plugin as FormKitPlugin, defaultConfig } from '@formkit/vue'
import { de, en } from '@formkit/i18n'

import config from '../../formkit.config';
import { App } from 'vue';

export function setupFormKit(app: App) {
  app.use(FormKitPlugin, defaultConfig({
    ...config,
    locales: { de, en },
    locale: 'de',
  }))
}
