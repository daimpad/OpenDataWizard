import { defineSetupVue3 } from '@histoire/plugin-vue'

import '@piveau/piveau-hub-ui-modules/styles';

import '@fontsource-variable/inter';
import '@fontsource-variable/space-grotesk';
import './styles/styles.scss';

import 'popper.js';
import 'bootstrap';
import 'leaflet/dist/leaflet.css';

import '#dpi-css';

import runtimeConfig from '../config/runtime-config';
import { glueConfig as GLUE_CONFIG } from '../config/user-config';

import { setupPiveauHubUiModules } from "./setup/setupPiveauHubUiModules";
import { runtimeConfigurationService } from '@piveau/piveau-hub-ui-modules';
import { userConfigShimPlugin } from '@piveau/dpi'

export const setupVue3 = defineSetupVue3(({ app, story, variant }) => {
  app.use(runtimeConfigurationService, runtimeConfig, { baseConfig: GLUE_CONFIG, debug: true, useExperimentalRuntimeParser: true });
  const userConfig = app.config.globalProperties.$env;
  app.use(userConfigShimPlugin, userConfig)

  setupPiveauHubUiModules(app, userConfig)
})
