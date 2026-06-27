import { type InjectionKey, type Plugin } from "vue";
import { type Config } from '../config-schema/index'

// add ts types
declare module 'vue' {
  interface ComponentCustomProperties {
    $env: Config;
  }
}

export const injectionKey: InjectionKey<Config> = Symbol('userConfig')

export const userConfigShimPlugin: Plugin = {
  install(app, userConfig: Config) {
    app.config.globalProperties.$env = userConfig;
    app.provide(injectionKey, userConfig);

    // app.component('AppConfirmationDialog', AppConfirmationDialog);
  }
}