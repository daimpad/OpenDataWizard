<script setup>
import { PhWarning } from '@phosphor-icons/vue'
import { computed, getCurrentInstance, onMounted, ref, unref, watch } from 'vue'
import { useEditModeInfo } from '../composables/useDpiEditMode'
import InputField from '../HappyFlowComponents/ui/InputField.vue'
import TextButtonSmall from '../HappyFlowComponents/ui/TextButtonSmall.vue'
import { useFormValues } from "../composables/useDpiFormValues";

const { formValues } = useFormValues();

const props = defineProps({
  context: Object,
})

const { isEditMode } = useEditModeInfo()

const konto = [
  'Mobilitätsreferat der Landeshauptstadt München',
  'contact@example.com',
  'https://example.com',
]

// Debounce timers for email and phone validation
const emailDebounceTimer = ref(null)
const phoneDebounceTimer = ref(null)

// datenbereitsteller reactive
const datenbereitsteller = computed(() => {
  
  try {
    const publisherData = formValues.value.BasicInfos?.['dct:publisher']?.[0]
    if (publisherData && publisherData['foaf:name'] && publisherData['foaf:mbox']) {
      return [
        publisherData['foaf:name'],
        publisherData['foaf:mbox']
      ]
    }
  } catch (error) {
    console.warn('Could not load publisher data:', error)
  }
  
  // Fallback to default values if no publisher data available
  return [
    '',
    '',
    ''
  ]
})
// ########## Todo: please change this assignment ##########
// const publisherData = "" || props.context.attrs.finalValues.BasicInfos['dct:publisher_8'][0];
const publisherData = ''

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
    if (keycloakContact.phone || keycloakContact.phoneNumber) {
      data.phone = keycloakContact.phone || keycloakContact.phoneNumber
    }
    return data
  }
  return {}
})

// Add validation errors state
const validationErrors = ref({
  1: {
    show: false,
    message: 'Bitte geben Sie einen gültigen Organisationsnamen ein.',
  },
  2: {
    show: false,
    message: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
  },
  3: {
    show: false,
    message: 'Bitte geben Sie eine gültige Telefonnummer ein.',
  },
})

let validInputs = ref({ 1: false, 2: false, 3: false })
const arr = ref([
  {
    'isValid': 'unset',
    'vcard:fn': '',
    'vcard:hasEmail': '',
    'vcard:hasTelephone': '',
  },
])

if (!isEditMode.value) props.context.node.input(arr);

if (props.context.node.value.length === 0) {
  props.context.node.input(unref(arr))
}

// Populate datenbereitsteller from context on mount
onMounted(() => {
  try {
    // Access the main object through context
    if (
      publisherData
      && publisherData['foaf:name']
      && publisherData['foaf:mbox']
    ) {
      datenbereitsteller.value = [
        publisherData['foaf:name'],
        publisherData['foaf:mbox']
      ]
    }
  }
  catch (error) {
    console.warn('Could not load publisher data from context:', error)
    // Keep the default hardcoded values if there's an error
  }
})

// watch publisherData to update datenbereitsteller if it changes
// watch(
//   () => props.context.attrs.finalValues.BasicInfos["dct:publisher_8"],
//   (newValue) => {
//     if (
//       newValue &&
//       newValue[0] &&
//       newValue[0]["foaf:name"] &&
//       newValue[0]["foaf:mbox"]
//     ) {
//       datenbereitsteller.value = [
//         newValue[0]["foaf:name"],
//         newValue[0]["foaf:mbox"],
//         newValue[0]["foaf:homepage"] || "", // Optional homepage
//       ];
//     }
//   },
//   { immediate: true }
// );

// Validation functions
function validateName(value) {
  return value.trim().length >= 2 // Name should be at least 2 characters
}

function validateEmail(value) {
  const emailRegex = /^[^\s@]+@[^\s@][^\s.@]*\.[^\s@]+$/
  return emailRegex.test(value)
}

function validatePhone(value) {
  // Allow empty (optional field)
  if (!value || value.trim() === '') {
    return true;
  }

  // Normalize: remove spaces, dashes, parentheses
  const cleaned = value.replace(/[\s\-().]/g, '');

  // Must start with + or a digit
  if (!/^[+]?\d+$/.test(cleaned)) {
    return false;
  }

  // Check length (international numbers are usually 7–15 digits)
  const digitsOnly = cleaned.replace(/\D/g, '');
  return digitsOnly.length >= 6 && digitsOnly.length <= 15;
}

// Function to perform validation
function performValidation(newValue, namespace, iIndex) {
  let isValid = false

  // Skip validation for empty optional fields (phone)
  if (iIndex === 3 && newValue.trim() === '') {
    validationErrors.value[iIndex].show = false
    validInputs.value[iIndex] = true // Consider empty phone as valid since it's optional
  }
  else if (newValue.trim() === '') {
    validationErrors.value[iIndex].show = true
    validInputs.value[iIndex] = false
  }
  else {
    // Perform specific validation based on field type
    switch (namespace) {
      case 'vcard:fn':
        isValid = validateName(newValue)
        break
      case 'vcard:hasEmail':
        isValid = validateEmail(newValue)
        break
      case 'vcard:hasTelephone':
        isValid = validatePhone(newValue)
        break
      default:
        isValid = newValue.trim() !== ''
    }

    // Update validation state
    validationErrors.value[iIndex].show = !isValid
    validInputs.value[iIndex] = isValid
  }

  // Update overall form validity
  // Required fields are name and email, phone is optional
  if (validInputs.value[1] && validInputs.value[2]) {
    arr.value[0].isValid = true
  }
  else {
    arr.value[0].isValid = 'unset'
  }
}

// Updated method to handle model updates with validation
function updateValue(newValue, namespace, iIndex) {
  // Update the field value immediately
  arr.value = arr.value.map((item) => {
    if (namespace in item) {
      return { ...item, [namespace]: newValue }
    }
    return item
  })

  // Notify parent component about the change immediately
  props.context.node.input(arr.value)

  // Apply debounce for email and phone validation
  if (iIndex === 2) {
    // Email field - debounce validation
    if (emailDebounceTimer.value) {
      clearTimeout(emailDebounceTimer.value)
    }
    emailDebounceTimer.value = setTimeout(() => {
      performValidation(newValue, namespace, iIndex)
    }, 800)
  } else if (iIndex === 3) {
    // Phone field - debounce validation
    if (phoneDebounceTimer.value) {
      clearTimeout(phoneDebounceTimer.value)
    }
    phoneDebounceTimer.value = setTimeout(() => {
      performValidation(newValue, namespace, iIndex)
    }, 800)
  } else {
    // Name field - validate immediately
    performValidation(newValue, namespace, iIndex)
  }
}

// Function to use Konto details (first TextButtonSmall)
function useContactDetails() {
  // Set the name and email from contactData
  const contact = contactData.value
  
  if (contact.organization) {
    updateValue(contact.organization, 'vcard:fn', 1)
  }
  
  if (contact.email) {
    updateValue(contact.email, 'vcard:hasEmail', 2)
  }
}


// Function to use Datenbereitsteller details (second TextButtonSmall)
function useDatenbereitstellerDetails() {
  // Set the name and email from datenbereitsteller
  updateValue(datenbereitsteller.value[0], 'vcard:fn', 1)
  updateValue(datenbereitsteller.value[1], 'vcard:hasEmail', 2)
}

// Handle regular input from fields (kept for backward compatibility)
function handleInput(e, namespace, iIndex) {
  updateValue(e.target.value, namespace, iIndex)
}

const validateAllFields = () => {
  const currentData = arr.value[0]
  
  // Validate name field (required)
  if (currentData['vcard:fn'].trim() === '') {
    validationErrors.value[1].show = true
    validInputs.value[1] = false
  } else {
    const nameValid = validateName(currentData['vcard:fn'])
    validationErrors.value[1].show = !nameValid
    validInputs.value[1] = nameValid
  }
  
  // Validate email field (required)
  if (currentData['vcard:hasEmail'].trim() === '') {
    validationErrors.value[2].show = true
    validInputs.value[2] = false
  } else {
    const emailValid = validateEmail(currentData['vcard:hasEmail'])
    validationErrors.value[2].show = !emailValid
    validInputs.value[2] = emailValid
  }
  
  // Validate phone field (optional)
  if (currentData['vcard:hasTelephone'].trim() === '') {
    validationErrors.value[3].show = false
    validInputs.value[3] = true // Optional field
  } else {
    const phoneValid = validatePhone(currentData['vcard:hasTelephone'])
    validationErrors.value[3].show = !phoneValid
    validInputs.value[3] = phoneValid
  }
  
  // Update overall validity
  if (validInputs.value[1] && validInputs.value[2]) {
    arr.value[0].isValid = true
  } else {
    arr.value[0].isValid = false
  }
}

watch(() => arr.value[0].isValid, (newValue) => {
  if (newValue === false) {
    validateAllFields()
  }
})
</script>

<template>
  <div class="dpiV3InnerComponentWrap">
    <h4>{{ $t("message.dataupload.datasets.dcat:contactPoint.title") }}</h4>
    <div class="copy-large-regular">
      {{ $t("message.dataupload.datasets.dcat:contactPoint.description") }}
    </div>
    <!-- Make this a component and adapt the rigt button! @mic -->
    <div class="dpiV3AutoCompleteWrap">
      <div class="CardTips_internal">
        <div class="CardTips_Part">
          <div class="firstRow">
            <div class="icon_title copy-small-semi-bold">
              {{ $t("message.dataupload.datasets.dcat:contactPoint.account") }}
            </div>
            <div class="TextButton_small">
              <TextButtonSmall
                :button-text="
                  $t(
                    'message.dataupload.datasets.dcat:contactPoint.useAccountButton',
                  )
                "
                icon-start="DeleteBlue"
                icon-name="copy"
                class="dpiV3_usedetailsButton"
                @click="useContactDetails"
              />
            </div>
          </div>
          <div class="secondRow">
            <span
              v-for="value in Object.values(contactData)"
              class="copy-small-regular"
            >
              {{ value }}
            </span>
          </div>
        </div>
        <div class="CardTips_Part">
          <div class="firstRow">
            <div class="icon_title copy-small-semi-bold">
              {{ $t("message.metadata.publisher") }}
            </div>
            <div class="TextButton_small">
              <TextButtonSmall
                :button-text="
                  $t(
                    'message.dataupload.datasets.dcat:contactPoint.usePublisherButton',
                  )
                "
                icon-start="true"
                icon-name="copy"
                class=""
                @click="useDatenbereitstellerDetails"
              />
            </div>
          </div>
          <div class="secondRow">
            <span v-for="item in datenbereitsteller" class="copy-small-regular">
              {{ item }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Name input with error container -->
    <div class="input-container">
      <InputField
        :model-value="arr[0]['vcard:fn']"
        :add-on-text="false"
        :date-picker="false"
        :info-icon="false"
        :placeholder="
          $t(
            'message.dataupload.datasets.dcat:contactPoint.nameInput.placeholder',
          )
        "
        :pre-icon="false"
        input-field-size="large"
        :initial-hint-text="false"
        :label="
          $t('message.dataupload.datasets.dcat:contactPoint.nameInput.title')
        "
        :show-end-icon="false"
        :show-error="validationErrors[1].show"
        @update:model-value="updateValue($event, 'vcard:fn', 1)"
      />
      <div v-if="validationErrors[1].show" class="dpiV3_errormsgWrapper">
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular">{{ validationErrors[1].message }}</span>
      </div>
    </div>

    <!-- Email input with error container -->
    <div class="input-container">
      <InputField
        :model-value="arr[0]['vcard:hasEmail']"
        :add-on-text="false"
        :date-picker="false"
        :info-icon="false"
        :placeholder="
          $t(
            'message.dataupload.datasets.dcat:contactPoint.mailInput.placeholder',
          )
        "
        :pre-icon="false"
        input-field-size="large"
        :initial-hint-text="false"
        :label="
          $t('message.dataupload.datasets.dcat:contactPoint.mailInput.title')
        "
        :show-end-icon="false"
        :show-error="validationErrors[2].show"
        @update:model-value="updateValue($event, 'vcard:hasEmail', 2)"
      />
      <div v-if="validationErrors[2].show" class="dpiV3_errormsgWrapper">
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular">{{ validationErrors[2].message }}</span>
      </div>
    </div>

    <!-- Phone input with error container (optional field) -->
    <div class="input-container">
      <InputField
        :model-value="arr[0]['vcard:hasTelephone']"
        :add-on-text="false"
        :date-picker="false"
        :info-icon="false"
        :placeholder="
          $t(
            'message.dataupload.datasets.dcat:contactPoint.telInput.placeholder',
          )
        "
        :pre-icon="false"
        input-field-size="large"
        :initial-hint-text="false"
        :label="
          $t('message.dataupload.datasets.dcat:contactPoint.telInput.title')
        "
        :show-end-icon="false"
        :show-error="validationErrors[3].show"
        @update:model-value="updateValue($event, 'vcard:hasTelephone', 3)"
      />
      <div v-if="validationErrors[3].show" class="dpiV3_errormsgWrapper">
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular">{{ validationErrors[3].message }}</span>
      </div>
    </div>

    <div class="copy-large-regular">
      {{
        $t("message.dataupload.datasets.dcat:contactPoint.descriptionBottom")
      }}
    </div>
    <div
      v-if="arr.find((obj) => (obj.isValid === false))"
      class="form-error-message"
    >
      <PhWarning :size="16" weight="fill" />
      <span class="copy-mini-regular">Bitte füllen Sie alle Pflichtfelder aus, bevor Sie fortfahren.</span>
    </div>
  </div>
</template>

<style scoped>
.dpiV3_usedetailsButton {
  span {
    font-family: var(--text-default-font-family);
    font-size: var(--copy-small-regular-font-size);
    font-style: normal;
    font-weight: var(--copy-small-regular-font-weight);
    line-height: var(--copy-small-regular-line-height);
  }
}

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
  background: var(--blue-10, #f3fbff);
}

.CardTips_internal {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: var(--Spacing-4, 24px);
  align-self: stretch;
}

.CardTips_Part {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
}

.firstRow {
  display: flex;
  align-items: center;
  gap: 16px;
  align-self: stretch;
  margin: 0px;
}

.icon_title {
  display: flex;
  align-items: center;
  gap: var(--Spacing-3, 16px);
  flex: 1 0 0;
}

.TextButton_small {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--Spacing-1, 4px);
}

.first_Content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  align-self: stretch;
}

.secondRow {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  align-self: stretch;

  span {
    color: var(--neutral-80, #3d4952);
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
  color: var(--text-error, #a9242c);
}

.dpiV3_errormsgWrapper span {
  color: var(--text-error, #a9242c);
  text-align: right;
}

.form-error-message {
  width: 375px;
  position: absolute;
  right: 10px;
  bottom: 104px;
  color: var(--text-error, #a9242c);
  display: flex;
  align-items: center;
  gap: 6px;
}

.form-error-message span {
  color: var(--text-error, #a9242c);
  text-align: right;
}
</style>