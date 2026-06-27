import Repeatable from "./Repeatable.vue";
import FormKitGroup from "./FormKitGroup.vue";
import { type FormKitLibrary } from "@formkit/core";
import AutocompleteInput from "../data-provider-interface/components/AutocompleteInput.vue";
import FileUpload from "../data-provider-interface/components/FileUpload.vue";
import UniqueIdentifierInput from "../data-provider-interface/components/UniqueIdentifierInput.vue";
import SpatialInput from "../data-provider-interface/components/SpatialInput.vue";
import ConditionalInput from "../data-provider-interface/components/ConditionalInput.vue";
import SimpleSelect from "../data-provider-interface/components/SimpleSelect.vue";
import SimpleInput from "../data-provider-interface/components/SimpleInput.vue";
import SimpleAccessURLInput from "../data-provider-interface/components/SimpleAccessURLInput.vue";
import HappyFlowLandingPage from "../data-provider-interface/components/HappyFlowLandingPage.vue";
import DiscoverabilityPage from "../data-provider-interface/components/DiscoverabilityPage.vue";
import HVDPage from "../data-provider-interface/components/HVDPage.vue";
import BasicInfosPage from "../data-provider-interface/components/BasicInfosPage.vue";
import CoveringPage from "../data-provider-interface/components/CoveringPage.vue";
import DistributionSimplePage from "../data-provider-interface/components/DistributionSimplePage.vue";
import ReviewAndPublishPage from "../data-provider-interface/components/ReviewAndPublishPage.vue";
import dpiV3Description from "../data-provider-interface/components/dpiV3Description.vue";
import TitelPage from "../data-provider-interface/components/TitelPage.vue";
import UpdateDate from "../data-provider-interface/components/UpdateDate.vue";
import PublisherPage from "../data-provider-interface/components/PublisherPage.vue";
import ContactPage from "../data-provider-interface/components/ContactPage.vue";
import TempResPage from "../data-provider-interface/components/TempResPage.vue";
import PolGeoUriPage from "../data-provider-interface/components/PolGeoUriPage.vue";
import DistLicense from "../data-provider-interface/components/DistLicense.vue";

export default {

    repeatable: {
        type: 'list',
        component: Repeatable
    },
    id: {
        type: 'input',
        component: UniqueIdentifierInput,
    },
    auto: {
        type: 'group',
        component: AutocompleteInput
    },
    fileupload: {
        type: 'group',
        component: FileUpload
    },
    spatialinput: {
        type: 'group',
        component: SpatialInput
    },
    formkitGroup: {
        type: 'group',
        component: FormKitGroup
    },
    simpleConditional: {
        type: 'group',
        component: ConditionalInput
    },
    simpleSelect: {
        type: 'input',
        component: SimpleSelect,
    },
    simpleInput: {
        type: 'input',
        component: SimpleInput,
    },
    simpleAccessURLInput: {
        type: 'group',
        component: SimpleAccessURLInput,
    },
    happyFlowLandingPage: {
        type: 'group',
        component: HappyFlowLandingPage,
    },
    discoverabilityPage: {
        type: 'group',
        component: DiscoverabilityPage,
    },
    hvdPage: {
        type: 'group',
        component: HVDPage,
    },
    basicInfosPage: {
        type: 'group',
        component: BasicInfosPage,
    },
    'dct:title': {
        type: 'input',
        component: TitelPage,
    },
    'dct:modified': {
        type: 'input',
        component: UpdateDate,
    },
    'dct:publisher': {
        type: 'group',
        component: PublisherPage,
    },
    'dcat:contactPoint': {
        type: 'group',
        component: ContactPage,
    },
    coveringPage: {
        type: 'group',
        component: CoveringPage,
    },
    'dct:description': {
        type: 'input',
        component: dpiV3Description,
    },
    'dcat:distribution': {
        type: 'group',
        component: DistributionSimplePage,
    },
    reviewAndPublishPage: {
        type: 'group',
        component: ReviewAndPublishPage,
    },
    'dcat:temporalResolution': {
        type: 'input',
        component: TempResPage
    },
    'dcatde:politicalGeocodingURI': {
        type: 'input',
        component: PolGeoUriPage
    },
    'dct:license': {
        type: 'group',
        component: DistLicense
    }

} as FormKitLibrary;