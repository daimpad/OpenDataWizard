<template>
    <div class="dpiV3_RapModalContainer">
        <div class="dpiV3_RapModalOuter">
          
            <div class="dpiV3_RapModalInner">
                <div class="dpiV3_modalHead">
                    <div class="headline" v-if="modalTitle">{{ modalTitle }}</div>
                    <div class="dpiV3_closeButtonContainer">
                        <CrossOutButton @click="handleCloseModal" class="dpiV3_closeButton" type="default" />
                    </div>
                   
                </div>
                <div class="dpiV3_modalBody">
                    <span v-if="props.activeSection === 'findabilityHvd'">{{ $t("message.metadata.categories") }}</span>
                    
                    <findability class="findability-container" v-if="props.activeSection === 'findabilityHvd'">
                        <FindabilityChips :context="context" ref="findabilityChipsRef" />
                        <HVDSwitch :context="context"/>
                    </findability>
                    <div id="essentials" v-if="props.activeSection === 'essentials'">
                        <EssentialsModal 
                            :context="context" 
                            :newValues="values" 
                            v-model="essentialsModel" 
                            ref="essentialsModalRef" />
                    </div>
                    <coverage v-if="props.activeSection === 'coverage'">
                        <CoverageModal 
                            :context="context" 
                            :newValues="values" 
                            ref="coverageModalRef" />
                    </coverage>
                    <distributionRap v-if="props.activeSection === 'distributions'">
                        <DistributionModal :context="context" :newValues="values" ref="distributionModalRef"></DistributionModal>
                    </distributionRap>
                    <additionals v-if="props.activeSection === 'additionals'">
                        <AdditionalsModal :context="context" @closeModal="closeModal"></AdditionalsModal>
                    </additionals>

                    <!-- Validation error messages at the bottom -->
                    <div v-if="showValidationError && currentValidationErrors.length > 0" class="dpiV3_errormsgWrapper">
                        <PhWarning :size="16" weight="fill" />
                        <div class="validation-messages">
                            <span v-for="error in currentValidationErrors" :key="error" class="copy-mini-regular">
                                {{ error }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <interaction class="dpiV3_interactionWrap" v-if="props.activeSection != 'additionals'">
                <div class="dpiV3_actionButtonWrap">
                    <ButtonV3 @click="handleCloseModal" buttonText="Zurück" size="large" iconStart="CaretLeft"
                        variant="tertiary" />
                    <ButtonV3 @click="saveToStore" buttonText="Speichern" size="large" />
                </div>
            </interaction>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { PhWarning } from "@phosphor-icons/vue";
import CrossOutButton from "./CrossOutButton.vue";
import ButtonV3 from "./ButtonV3.vue";
import FindabilityChips from "./SectionItems/FindabilityChips.vue"
import HVDSwitch from "./SectionItems/HVDSwitch.vue"
import EssentialsModal from "./SectionItems/EssentialsModal.vue"
import DistributionModal from "./SectionItems/DistributionModal.vue"
import CoverageModal from "./SectionItems/CoverageModal.vue"
import AdditionalsModal from "./SectionItems/AdditionalsModal.vue"
import { getNode } from '@formkit/core'
import { useFormValues } from '../../composables/useDpiFormValues';

const { t } = useI18n();

const props = defineProps({
    isVisible: {
        type: Boolean,
        required: true
    },
    context: Object,
    activeSection: String
});

let values = ref()
const { formValues } = useFormValues()
const emit = defineEmits(['close']);
const findabilityChipsRef = ref(null);
const essentialsModalRef = ref(null);
const coverageModalRef = ref(null);
const showValidationError = ref(false);
const distributionModalRef = ref(null);

// title mapping
const modalTitleMap = {
    'findabilityHvd': 'message.dataupload.datasets.rap.findability.title',
    'essentials': 'message.dataupload.datasets.rap.essentials.title', 
    'coverage': 'message.dataupload.datasets.rap.coverage.title',
    'distributions': 'message.metadata.distributions',
    'additionals': 'message.metadata.additionals'
};

console.log(formValues.value)

// Computed property for dynamic title
const modalTitle = computed(() => {
    const titleKey = modalTitleMap[props.activeSection];
    return titleKey ? t(titleKey) : '';
});

// Check if at least one findability category is selected
const hasSelectedCategories = computed(() => {
    const discoverabilityPage = formValues.value?.Discoverability?.discoverabilityPage;
    if (!discoverabilityPage || !Array.isArray(discoverabilityPage)) {
        return false;
    }
    
    // Check if there are any items beyond the initial isValid object
    const selectedItems = discoverabilityPage.filter(item => item.id && item.label);
    return selectedItems.length > 0;
});

// Validation function for findability section
const validateFindabilitySection = () => {
    if (props.activeSection === 'findabilityHvd') {
        if (!hasSelectedCategories.value) {
            showValidationError.value = true;
            return false;
        }
    }
    showValidationError.value = false;
    return true;
};

const validateDistributionSection = () => {
    if (props.activeSection === 'distributions') {
        console.log(distributionModalRef.value && !distributionModalRef.value.isValid);
        // Checks the Exposed 'isValid' property from DistributionModal
        if (distributionModalRef.value && !distributionModalRef.value.isValid) {
            showValidationError.value = true;
            return false;
        }
    }
    showValidationError.value = false;
    return true;
};

// Validation function for essentials section
const validateEssentialsSection = () => {
    if (props.activeSection === 'essentials') {
        if (essentialsModalRef.value && !essentialsModalRef.value.isValid) {
            showValidationError.value = true;
            return false;
        }
    }
    showValidationError.value = false;
    return true;
};

// Validation function for coverage section
const validateCoverageSection = () => {
    if (props.activeSection === 'coverage') {
        if (coverageModalRef.value && coverageModalRef.value.validateAllItems()) {
            // validateAllItems returns TRUE if there ARE errors
            showValidationError.value = true;
            return false;
        }
    }
    showValidationError.value = false;
    return true;
};

// Overall validation function
const validateCurrentSection = () => {
    if (props.activeSection === 'findabilityHvd') {
        return validateFindabilitySection();
    } else if (props.activeSection === 'essentials') {
        return validateEssentialsSection();
    } else if (props.activeSection === 'coverage') {
        return validateCoverageSection();
    } else if (props.activeSection === 'distributions') {
        console.log("hey"+validateDistributionSection());
        return validateDistributionSection();
    }
    showValidationError.value = false;
    return true;
};

// Get current validation error messages
const currentValidationErrors = computed(() => {
    if (props.activeSection === 'findabilityHvd' && !hasSelectedCategories.value) {
        return ['Bitte wählen Sie mindestens eine Kategorie aus, bevor Sie fortfahren.'];
    } else if (props.activeSection === 'essentials' && essentialsModalRef.value && !essentialsModalRef.value.isValid) {
        return ['Bitte füllen Sie alle Pflichtfelder aus, bevor Sie fortfahren.'];
    } else if (props.activeSection === 'coverage') {
        if (coverageModalRef.value && coverageModalRef.value.validateAllItems()) {
            return ['Bitte füllen Sie alle erforderlichen Felder aus, wenn ein Feld ausgefüllt ist.'];
        }
    }
    return [];
});

// Handle modal close with validation
const handleCloseModal = () => {
    if (validateCurrentSection()) {
        closeModal();
    }
};

const closeModal = () => {
    showValidationError.value = false;
    emit('close');
};

const computedBasicInfos = computed(() => {
    return formValues.value['BasicInfos']
})

const getLocalizedValue = (field, language) => {
    return field?.find((item) => item['@language'] === language)?.['@value'] || '';
};

const getFirstValue = (field, property) => {
    return field?.[0]?.[property] || '';
};

const getSimpleValue = (field) => {
    return field?.[0]?.['@value'] || '';
};

const essentialsModel = ref({
    title: getLocalizedValue(computedBasicInfos.value['dct:title'], 'de'),
    description: getLocalizedValue(computedBasicInfos.value['dct:description'], 'de'),
    modified: getSimpleValue(computedBasicInfos.value['dct:modified']),
    publisherName: getFirstValue(computedBasicInfos.value['dct:publisher'], 'foaf:name'),
    publisherMail: getFirstValue(computedBasicInfos.value['dct:publisher'], 'foaf:mbox'),
    publisherWebsite: getFirstValue(computedBasicInfos.value['dct:publisher'], 'foaf:homepage'),
    contactPointName: getFirstValue(computedBasicInfos.value['dcat:contactPoint'], 'vcard:fn'),
    contactPointMail: getFirstValue(computedBasicInfos.value['dcat:contactPoint'], 'vcard:hasEmail'),
    contactPointPhone: getFirstValue(computedBasicInfos.value['dcat:contactPoint'], 'vcard:hasTelephone'),
});

const updateLocalizedField = (basicInfos, fieldName, value, language) => {
    if (!basicInfos[fieldName]) {
        basicInfos[fieldName] = [];
    }
    const existingEntry = basicInfos[fieldName].find((item) => item['@language'] === language);
    
    if (!existingEntry) {
        basicInfos[fieldName].push({ '@value': value, '@language': language });
    } else {
        existingEntry['@value'] = value;
    }
};

const saveToStore = () => {
    // 1. Validate
    if (!validateCurrentSection()) {
        return;
    }

    // 2. Only save Essentials data if we are in the Essentials section
    // (DistributionModal updates 'formValues' directly/reactively, so no manual save is needed here)
    if (props.activeSection === 'essentials') {
        // Safe check for BasicInfos existence
        const currentBasicInfos = formValues.value['BasicInfos'] || {};
        const basicInfos = JSON.parse(JSON.stringify(currentBasicInfos));
        
        updateLocalizedField(basicInfos, 'dct:title', essentialsModel.value.title, 'de');
        updateLocalizedField(basicInfos, 'dct:description', essentialsModel.value.description, 'de');
        
        basicInfos['dct:publisher'] = [{
            'foaf:name': essentialsModel.value.publisherName,
            'foaf:mbox': essentialsModel.value.publisherMail,
            'foaf:homepage': essentialsModel.value.publisherWebsite,
        }];
        
        basicInfos['dcat:contactPoint'] = [{
            'vcard:fn': essentialsModel.value.contactPointName,
            'vcard:hasEmail': essentialsModel.value.contactPointMail,
            'vcard:hasTelephone': essentialsModel.value.contactPointPhone,
        }];
        
        basicInfos['dct:modified'] = [{ '@value': essentialsModel.value.modified }];
        
        formValues.value['BasicInfos'] = basicInfos;
    }

    // 3. Close the modal
    emit('close');
}
</script>

<style scoped>
#essentials {
    width: 100%;
}

.dpiV3_modalHead {
    width: 100%;
    position: relative;
    padding-top: 24px;
    height: 48px;
    margin-top: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.dpiV3_modalHead h4 {
    margin: 0;
    color: var(--Colour-neutral-Neutral80, #3D4952);
    font-family: Inter;
    font-size: 18px;
    font-style: normal;
    font-weight: 600;
    line-height: 28px;
    flex: 1;
}

.headline {
    overflow: hidden;
    color: var(--Colour-neutral-Neutral80, #3D4952);
    text-overflow: ellipsis;
    /* Headlines/Headline-4 */
    font-family: Inter;
    font-size: 24px;
    font-style: normal;
    font-weight: 700;
    line-height: 36px; /* 150% */
}

.dpiV3_closeButtonContainer {
    position: absolute;
    right: 0;
    /* top: 0; */
    /* z-index: 1000; */
}

.dpiV3_findabilitySwitchWrapper {
    display: flex;
    align-items: center;
    gap: var(--Spacing-3, 16px);
    align-self: stretch;
}

.dpiV3_modalBody {
 
    width: 100%;
    position: relative;
    z-index: 1;
}

.dpiV3_actionButtonWrap {
    display: flex;
    align-items: center;
    gap: var(--Spacing-3, 16px);
}

.dpiV3_interactionWrap {
    display: flex;
    justify-content: space-between;
    align-items: center;
    align-self: flex-end;
}

.dpiV3_RapModalInner {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: var(--Spacing-5, 32px);
    align-self: stretch;
    position: relative;
}

dpiV3_modalBody {
    findability {
        display: flex;
        flex-direction: column;
        gap: var(--Spacing-5, 32px);
    }

    span {
        color: var(--Colour-neutral-Neutral80, #3D4952);
        font-family: Inter;
        font-size: 15px;
        font-style: normal;
        font-weight: 400;
        line-height: 24px;
    }
}

.dpiV3_RapModalOuter {
    display: flex;
    width: 624px;
    padding: 0 var(--Spacing-5, 32px) var(--Spacing-5, 32px) var(--Spacing-5, 32px);
    flex-direction: column;
    align-items: flex-start;
    /* gap: var(--Spacing-8, 64px); */
    position: relative;
    border-radius: var(--Modal-Radius, 32px);
    background: var(--neutral-l0, #FFF);
    margin-top: 84px;
}

.dpiV3_RapModalContainer::-webkit-scrollbar {
    display: none;
}

.dpiV3_RapModalContainer {
    z-index: 9999;
    position: fixed;
    overflow: scroll;
    width: 100%;
    left: 0;
    height: 100%;
    top: 0;
    background: rgba(11, 26, 37, 0.7);
    display: flex;
    align-items: start;
    justify-content: center;
}

.findability-container{
    display: flex;
    justify-content: flex-start;
    flex-direction: column;
    column-gap: 2rem;
    gap: 2rem;
}

/* Validation error styling */
.validation-messages {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.dpiV3_errormsgWrapper {
  display: flex;
  justify-content: end;
  margin-bottom: -5rem;
  margin-top: 5rem;
  width: 350px;
  display: flex;
  margin-bottom: -50px;
  align-items: center;
  gap: 6px;
  width: auto;
  color: var(--text-error, #a9242c);

  span {
    color: var(--text-error, #a9242c);
    text-align: right;
  }
}
</style>