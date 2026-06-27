import type { App } from 'vue'

import inputDefinitions from '../form/inputDefinitions'
// Import data-provider-interface components
import AutocompleteInput from './components/AutocompleteInput.vue'
import ConditionalInput from './components/ConditionalInput.vue'
import DataFetchingComponent from './components/DataFetchingComponent.vue'
import Dropup from './components/Dropup.vue'
import FileUpload from './components/FileUpload.vue'
import InfoSlot from './components/InfoSlot.vue'
import LanguageSelector from './components/LanguageSelector.vue'
import Navigation from './components/Navigation.vue'
import ReviewAndPublishPage from './components/ReviewAndPublishPage.vue'

import UniqueIdentifierInput from './components/UniqueIdentifierInput.vue'
// import data provider-interface
import DataProviderInterface from './DataProviderInterface.vue'
import DpiMenu from './DPIMenu.vue'
import ComponentLibrary from './HappyFlowComponents/ComponentLibrary.vue'

import DPIHome from './HappyFlowComponents/DPIHome.vue'
// import data provider-interface views
import DistOverview from './views/DistributionOverview.vue'
import DraftsPage from './views/DraftsPage.vue'
import InputPage from './views/InputPage.vue'
import LinkedDataViewer from './views/LinkedDataViewer.vue'
import OverviewPage from './views/OverviewPage.vue'
// import data provider-interface views OverviewPage
import CatalogueOverview from './views/OverviewPage/CatalogueOverview.vue'
import DatasetOverview from './views/OverviewPage/DatasetOverview.vue'
import DistributionOverview from './views/OverviewPage/DistributionOverview.vue'

import PropertyEntry from './views/OverviewPage/PropertyEntry.vue'
import UserCataloguesPage from './views/UserCataloguesPage.vue'

import UserProfilePage from './views/UserProfilePage.vue'

export * from './composables/index'
export { config as dpiSpecConfig } from './config/dpi-spec-config'

export function registerComponents(app: App) {
  app.component('AutocompleteInput', AutocompleteInput)
  app.component('ConditionalInput', ConditionalInput)
  app.component('DataFetchingComponent', DataFetchingComponent)
  app.component('Dropup', Dropup)
  app.component('FileUpload', FileUpload)
  app.component('InfoSlot', InfoSlot)
  app.component('LanguageSelector', LanguageSelector)
  app.component('Navigation', Navigation)
  app.component('UniqueIdentifierInput', UniqueIdentifierInput)

  app.component('CatalogueOverview', CatalogueOverview)
  app.component('DatasetOverview', DatasetOverview)
  app.component('DistributionOverview', DistributionOverview)
  app.component('PropertyEntry', PropertyEntry)

  app.component('DistOverview', DistOverview)
  app.component('DraftsPage', DraftsPage)
  app.component('InputPage', InputPage)
  app.component('LinkedDataViewer', LinkedDataViewer)
  app.component('OverviewPage', OverviewPage)
  app.component('UserCataloguesPage', UserCataloguesPage)
  app.component('UserProfilePage', UserProfilePage)

  app.component('DataProviderInterface', DataProviderInterface)
  app.component('DpiMenu', DpiMenu)
}

// export components
export { AutocompleteInput, ConditionalInput, DataFetchingComponent, Dropup, FileUpload, InfoSlot, LanguageSelector, Navigation, UniqueIdentifierInput }

// export views
export { CatalogueOverview, DatasetOverview, DistributionOverview, PropertyEntry }

export {
  ComponentLibrary,
  DistOverview,
  DPIHome,
  DraftsPage,
  InputPage,
  LinkedDataViewer,
  OverviewPage,
  ReviewAndPublishPage,
  UserCataloguesPage,
  UserProfilePage,
}

export { inputDefinitions }

// export data provider-interface
export { DataProviderInterface, DpiMenu }

export { default as store } from './store/index';

export * from './HappyFlowComponents/ui'

export { injectionKey as injectionKeyRuntimeConfig, userConfigShimPlugin } from './plugins/userConfigShimPlugin'
