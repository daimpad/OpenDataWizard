<template>
    <div class="dpiV3InnerComponentWrap">
        <h4>{{ $t("message.dataupload.datasets.dct:publisher.title") }}</h4>
        <div class="copy-large-regular"> {{ $t("message.dataupload.datasets.dct:publisher.description") }}</div>
        <!-- Make this a component and adapt the rigt button! @mic -->
        <div class="dpiV3AutoCompleteWrap">
            <div class="firstRow">
                <div>
                    <span class="copy-small-semi-bold">Konto</span>
                </div>
                <TextButtonSmall buttonText="Diese Details verwenden" iconStart="true" iconName="copy" @click="useTokenData">
                </TextButtonSmall>
            </div>
            <div class="secondRow">
                <span class="copy-small-regular" v-for="value in Object.values(contactData)">
                    {{ value }}
                </span>
            </div>
        </div>
        
        <!-- Organization name input with error container -->
        <div class="input-container">
            <InputField 
                :modelValue="arr[0]['foaf:name']" 
                @update:modelValue="updateValue($event, 'foaf:name', 1)"
                :addOnText="false" 
                :datePicker="false"
                :infoIcon="false" 
                :placeholder="$t('message.dataupload.datasets.dct:publisher.nameInput.placeholder')"
                :preIcon="false" 
                inputFieldSize="large" 
                :initialHintText="false"
                :label="$t('message.dataupload.datasets.dct:publisher.nameInput.title')" 
                :showEndIcon="false"
                :showError="validationErrors[1].show" 
            />
            <div class="dpiV3_errormsgWrapper" v-if="validationErrors[1].show">
                <PhWarning :size="16" weight="fill" />
                <span class="copy-mini-regular">{{ validationErrors[1].message }}</span>
            </div>
        </div>
        
        <!-- Email input with error container -->
        <div class="input-container">
            <InputField 
                :modelValue="arr[0]['foaf:mbox']" 
                @update:modelValue="updateValue($event, 'foaf:mbox', 2)"
                :addOnText="false" 
                :datePicker="false"
                :infoIcon="false" 
                :placeholder="$t('message.dataupload.datasets.dct:publisher.mailInput.placeholder')"
                :preIcon="false" 
                inputFieldSize="large" 
                :initialHintText="false"
                :showError="validationErrors[2].show"
                :label="$t('message.dataupload.datasets.dct:publisher.mailInput.title')" 
                :showEndIcon="false" 
            />
            <div class="dpiV3_errormsgWrapper" v-if="validationErrors[2].show">
                <PhWarning :size="16" weight="fill" />
                <span class="copy-mini-regular">{{ validationErrors[2].message }}</span>
            </div>
        </div>
        
        <!-- Website input with error container -->
        <div class="input-container">
            <InputField 
                :modelValue="arr[0]['foaf:homepage']" 
                @update:modelValue="updateValue($event, 'foaf:homepage', 3)"
                :addOnText="true" 
                :datePicker="false"
                :infoIcon="false" 
                :placeholder="$t('message.dataupload.datasets.dct:publisher.websiteInput.placeholder')"
                :preIcon="false" 
                inputFieldSize="large" 
                :initialHintText="false" 
                addOnLeadingText="https://"
                :label="$t('message.dataupload.datasets.dct:publisher.websiteInput.title')" 
                :showEndIcon="false"
                :showError="validationErrors[3].show" 
            />
            <div class="dpiV3_errormsgWrapper" v-if="validationErrors[3].show">
                <PhWarning :size="16" weight="fill" />
                <span class="copy-mini-regular">{{ validationErrors[3].message }}</span>
            </div>
        </div>
        
        <div class="copy-large-regular"> {{ $t("message.dataupload.datasets.dct:publisher.descriptionBottom") }}</div>
        <div class="form-error-message" v-if="arr.find(obj => obj.isValid === false)">
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">Bitte füllen Sie alle Pflichtfelder aus, bevor Sie fortfahren.</span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import InputField from "../HappyFlowComponents/ui/InputField.vue";
import TextButtonSmall from "../HappyFlowComponents/ui/TextButtonSmall.vue";
import { PhWarning } from "@phosphor-icons/vue";
import { getCurrentInstance } from "vue";
import { useEditModeInfo } from '../composables';
const props = defineProps({
    context: Object
});

const { isEditMode } = useEditModeInfo()
let validInputs = ref({ 1: false, 2: false, 3: false });

// Debounce timers for email and URL validation
const emailDebounceTimer = ref(null)
const urlDebounceTimer = ref(null)

const contactData = computed(() => {
  const keycloakContact
    = getCurrentInstance().appContext.config.globalProperties.$keycloak?.idTokenParsed?.contact
  if (keycloakContact) {
    const data = {
      organization: keycloakContact.organization,
    }
    if (keycloakContact.email) {
      data.email = keycloakContact.email
    }
    if (keycloakContact.website) {
      data.website = `https://${keycloakContact.website}`
    }
    return data
  }
  return {}
})

const arr = ref([
    { isValid: 'unset', "foaf:name": "", "foaf:mbox": "", "foaf:homepage": "" },
]);

// Add validation errors state
const validationErrors = ref({
    1: { show: false, message: "Bitte geben Sie einen gültigen Organisationsnamen ein." },
    2: { show: false, message: "Bitte geben Sie eine gültige E-Mail-Adresse ein." },
    3: { show: false, message: "Bitte geben Sie eine gültige URL ein." }
});

// Validation functions
const validateName = (value) => {
    return value.trim().length >= 2; // Name should be at least 2 characters
};

const validateEmail = (value) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(value);
};

const validateUrl = (value) => {
    // Allow simple domain validation for the URL field
    // This checks for valid domain pattern without the https:// prefix
    const urlRegex = /^(https?:\/\/)?([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}(\/[^\s]*)?$/;
    return urlRegex.test(value);
};

if (!isEditMode.value) props.context.node.input(arr);

// Function to perform validation
const performValidation = (newValue, namespace, iIndex) => {
    let isValid = false;
    
    if (newValue.trim() === "") {
        validationErrors.value[iIndex].show = false;
        validInputs.value[iIndex] = false;
    } else {
        // Perform specific validation based on field type
        switch(namespace) {
            case 'foaf:name':
                isValid = validateName(newValue);
                break;
            case 'foaf:mbox':
                isValid = validateEmail(newValue);
                break;
            case 'foaf:homepage':
                isValid = validateUrl(newValue);
                break;
            default:
                isValid = newValue.trim() !== "";
        }
        
        // Update validation state
        validationErrors.value[iIndex].show = !isValid;
        validInputs.value[iIndex] = isValid;
    }
    
    // Update overall form validity
    if (validInputs.value[1] && validInputs.value[2] && validInputs.value[3]) {
        arr.value[0].isValid = true;
    } else {
        arr.value[0].isValid = 'unset';
    }
};

// Updated method to handle model updates with validation
const updateValue = (newValue, namespace, iIndex) => {
    // Update the field value immediately
    arr.value = arr.value.map(item => {
        if (namespace in item) {
            return { ...item, [namespace]: newValue };
        }
        return item;
    });
    
    // Notify parent component about the change immediately
    if (props.context.node.value.length === 0) props.context.node.input(arr.value);

    // Apply debounce for email and URL validation
    if (iIndex === 2) {
        // Email field - debounce validation
        if (emailDebounceTimer.value) {
            clearTimeout(emailDebounceTimer.value);
        }
        emailDebounceTimer.value = setTimeout(() => {
            performValidation(newValue, namespace, iIndex);
        }, 800);
    } else if (iIndex === 3) {
        // URL field - debounce validation
        if (urlDebounceTimer.value) {
            clearTimeout(urlDebounceTimer.value);
        }
        urlDebounceTimer.value = setTimeout(() => {
            performValidation(newValue, namespace, iIndex);
        }, 800);
    } else {
        // Name field - validate immediately
        performValidation(newValue, namespace, iIndex);
    }
};

const validateAllFields = () => {
    const currentData = arr.value[0];
    
    // Validate name field (required)
    if (currentData['foaf:name'].trim() === '') {
        validationErrors.value[1].show = true;
        validInputs.value[1] = false;
    } else {
        const nameValid = validateName(currentData['foaf:name']);
        validationErrors.value[1].show = !nameValid;
        validInputs.value[1] = nameValid;
    }
    
    // Validate email field (required)
    if (currentData['foaf:mbox'].trim() === '') {
        validationErrors.value[2].show = true;
        validInputs.value[2] = false;
    } else {
        const emailValid = validateEmail(currentData['foaf:mbox']);
        validationErrors.value[2].show = !emailValid;
        validInputs.value[2] = emailValid;
    }
    
    // Validate homepage field (required)
    if (currentData['foaf:homepage'].trim() === '') {
        validationErrors.value[3].show = true;
        validInputs.value[3] = false;
    } else {
        const homepageValid = validateUrl(currentData['foaf:homepage']);
        validationErrors.value[3].show = !homepageValid;
        validInputs.value[3] = homepageValid;
    }
    
    // Update overall validity
    if (validInputs.value[1] && validInputs.value[2] && validInputs.value[3]) {
        arr.value[0].isValid = true;
    } else {
        arr.value[0].isValid = false;
    }
    
    console.log("Validation states:", validInputs.value);
    console.log("Validation errors:", validationErrors.value);
};

watch(() => arr.value[0].isValid, (newValue) => {
    console.log("Overall validity changed:", newValue);
    if (newValue === false) {
        validateAllFields();
    }
});

// Function to use test data when button is clicked
const useTokenData = () => {
    
    if (contactData.value) {
        // First field: Organization name
        if (contactData.value.organization) {
            updateValue(contactData.value.organization, 'foaf:name', 1);
        }
        
        // Second field: Email (if available)
        if (contactData.value.email) {
            updateValue(contactData.value.email, 'foaf:mbox', 2);
        }
        
        // Third field: Website (strip "https://" prefix since the input has addOnLeadingText)
        if (contactData.value.website) {
            let website = contactData.value.website;
            if (website.startsWith('https://')) {
                website = website.substring(8); // Remove "https://"
            } else if (website.startsWith('http://')) {
                website = website.substring(7); // Remove "http://"
            }
            updateValue(website, 'foaf:homepage', 3);
        }
    }
};
</script>

<style scoped>
.dpiV3AutoCompleteWrap {
    display: flex;
    min-width: 416px;
    max-width: 600px;
    padding: var(--Spacing-5, 32px);
    flex-direction: column;
    align-items: flex-start;
    gap: var(--Spacing-3, 16px);
    align-self: stretch;

    border-radius: var(--Modal-Radius, 32px);
    background: var(--blue-10, #F3FBFF);

    .firstRow {
        div {
            display: flex;
            align-items: center;
            gap: var(--Spacing-3, 16px);
            flex: 1 0 0;
        }

        span {
            color: var(--neutral-80, #3D4952);
        }

        display: flex;
        align-items: center;
        gap: 16px;
        align-self: stretch;
        margin: 0;
    }

    .secondRow {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: var(--Spacing-1, 4px);
        align-self: stretch;

        span {
            color: var(--neutral-80, #3D4952);
        }
    }
}

.input-container {
    position: relative;
    width: 100%;
}

.dpiV3_errormsgWrapper {
    display: flex;
    align-items: center;
    gap: 6px;
    width: auto;
    position: absolute;
    right: 0;
    bottom: -25px;
    color: var(--text-error, #A9242C);
}

.dpiV3_errormsgWrapper span {
    color: var(--text-error, #A9242C);
    text-align: right;
}

.form-error-message {
    width: 375px;
    position: absolute;
    right: 10px;
    bottom: 104px;
    color: var(--text-error, #A9242C);
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-error-message span {
    color: var(--text-error, #A9242C);
    text-align: right;
}
</style>