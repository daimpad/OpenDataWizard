import { defineSetupVue3 } from '@histoire/plugin-vue'
import { createI18n } from 'vue-i18n'
import 'jquery'
import 'bootstrap'

import './stories/styles.scss'

/** @ts-expect-error silence module nag */
import 'bootstrap/dist/css/bootstrap.min.css'

export const setupVue3 = defineSetupVue3(({ app }) => {
  const i18n = createI18n({
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
      en: {
        message: {
          hello: 'hello world',
        },
      },
    },
  })

  app.use(i18n)
})
