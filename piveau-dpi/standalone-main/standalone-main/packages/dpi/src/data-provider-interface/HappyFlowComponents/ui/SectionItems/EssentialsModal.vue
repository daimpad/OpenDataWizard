<template>
  <div class="dpiV3_innerRapModalWrapper">
    <div class="dpiV3_innerRapModalWrapper__section">
      <!-- Title -->
      <InputField
        class=""
        :label="$t('message.metadata.title')"
        v-model="model.title"
        :placeholder="''"
        :infoIcon="false"
        :preIcon="false"
        :initialHintText="false"
        :showEndIcon="false"
        :showError="model.showErrorTitle"
      />
      <div
        v-if="model.showErrorTitle && model.titleErrorMessage"
        class="dpiV3_field_error_text"
      >
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular">{{ model.titleErrorMessage }}</span>
      </div>
      
      <!-- Description -->
      <div class="topSpacing">
        <TextAreaV3
          class=""
          :label="$t('message.metadata.description')"
          v-model="model.description"
          :showError="model.showErrorDescription"
        />
        <div v-if="model.showErrorDescription && model.descriptionErrorMessage" class="dpiV3_field_error_text">
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ model.descriptionErrorMessage }}</span>
        </div>
      </div>
      
      <!-- Modified -->
      <div class="topSpacing">
        <InputField
          :label="$t('message.dataupload.datasets.rap.essentials.modifiedHeader')"
          :infoIcon="false"
          :preIcon="false"
          :datePicker="true"
          :showEndIcon="false"
          v-model="model.modified"
          :defaultInput="true"
          inputType="date"
          :showError="model.showErrorModified"
          :initialHintText="false"
        />
        <div v-if="model.showErrorModified && model.modifiedErrorMessage" class="dpiV3_field_error_text">
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ model.modifiedErrorMessage }}</span>
        </div>
      </div>
    </div>
    
    <!-- Publisher -->
    <div class="dpiV3_innerRapModalWrapper__section">
      <h5 class="headline-5 gap3BottomAsMargin">
        {{ $t("message.metadata.publisher") }}
      </h5>
      <div class="topSpacing">
        <InputField
          :label="$t('message.dataupload.datasets.dct:publisher.nameInput.title')"
          :infoIcon="false"
          :preIcon="false"
          :placeholder="$t('message.dataupload.datasets.dct:publisher.nameInput.placeholder')"
          :initialHintText="false"
          :showEndIcon="false"
          class=""
          v-model="model.publisherName"
          :showError="model.showErrorPublisherName"
          :error_message="'Bitte geben Sie einen gültigen Organisationsnamen ein.'"
        />
        <div
          v-if="model.showErrorPublisherName && model.publisherNameErrorMessage"
          class="dpiV3_field_error_text"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ model.publisherNameErrorMessage }}</span>
        </div>
      </div>
      
      <div class="topSpacing">
        <InputField
          :label="$t('message.dataupload.datasets.dct:publisher.mailInput.title')"
          :placeholder="$t('message.dataupload.datasets.dct:publisher.mailInput.placeholder')"
          :infoIcon="false"
          :preIcon="false"
          :initialHintText="false"
          :showEndIcon="false"
          class=""
          v-model="model.publisherMail"
          :showError="model.showErrorPublisherMail"
          :error_message="'Bitte geben Sie eine gültige E-Mail-Adresse ein.'"
        />
        <div
          v-if="model.showErrorPublisherMail && model.publisherMailErrorMessage"
          class="dpiV3_field_error_text"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ model.publisherMailErrorMessage }}</span>
        </div>
      </div>
      
      <div class="topSpacing">
        <InputField
          :label="$t('message.dataupload.datasets.dct:publisher.websiteInput.title')"
          :placeholder="$t('message.dataupload.datasets.dct:publisher.websiteInput.placeholder')"
          :infoIcon="false"
          :preIcon="false"
          :initialHintText="false"
          :showEndIcon="false"
          v-model="model.publisherWebsite"
          :showError="model.showErrorPublisherWebsite"
          :error_message="'Bitte geben Sie eine gültige URL ein.'"
        />
        <div
          v-if="model.showErrorPublisherWebsite && model.publisherWebsiteErrorMessage"
          class="dpiV3_field_error_text"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ model.publisherWebsiteErrorMessage }}</span>
        </div>
      </div>
    </div>
    
    <div class="dpiV3_innerRapModalWrapper__section">
      <h5 class="headline-5 gap3BottomAsMargin">
        {{ $t("message.header.subnav.contact") }}
      </h5>

      <div class="topSpacing">
        <InputField
          v-model="model.contactPointName"
          :label="$t('message.dataupload.datasets.dcat:contactPoint.nameInput.title')"
          :placeholder="
            $t(
              'message.dataupload.datasets.dcat:contactPoint.nameInput.placeholder',
            )
          "
          :infoIcon="false"
          :preIcon="false"
          :initialHintText="false"
          :showEndIcon="false"
          class=""
          :showError="model.showErrorContactPointName"
          :error_message="'Bitte geben Sie einen gültigen Namen ein (mindestens 2 Zeichen).'"
        />
        <div
          v-if="model.showErrorContactPointName && model.contactPointNameErrorMessage"
          class="dpiV3_field_error_text"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ model.contactPointNameErrorMessage }}</span>
        </div>
      </div>
      
      <div class="topSpacing">
        <InputField
          :label="$t('message.dataupload.datasets.dcat:contactPoint.mailInput.title')"
          :infoIcon="false"
          :preIcon="false"
          :initialHintText="false"
          :placeholder="
            $t(
              'message.dataupload.datasets.dcat:contactPoint.mailInput.placeholder',
            )
          "
          :showEndIcon="false"
          class=""
          v-model="model.contactPointMail"
          :showError="model.showErrorContactPointMail"
          :error_message="'Bitte geben Sie eine gültige E-Mail-Adresse ein.'"
        />
        <div
          v-if="model.showErrorContactPointMail && model.contactPointMailErrorMessage"
          class="dpiV3_field_error_text"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ model.contactPointMailErrorMessage }}</span>
        </div>
      </div>
      
      <div class="topSpacing">
        <InputField
          :label="$t('message.dataupload.datasets.dcat:contactPoint.telInput.title')"
          :infoIcon="false"
          :placeholder="
            $t(
              'message.dataupload.datasets.dcat:contactPoint.telInput.placeholder',
            )
          "
          :preIcon="false"
          :initialHintText="false"
          :showEndIcon="false"
          v-model="model.contactPointPhone"
          :showError="model.showErrorContactPointPhone"
          :error_message="'Bitte geben Sie eine gültige Telefonnummer ein.'"
        />
      <div
        v-if="model.showErrorContactPointPhone && model.contactPointPhoneErrorMessage"
        class="dpiV3_field_error_text"
      >
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular">{{ model.contactPointPhoneErrorMessage }}</span>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import InputField from "../InputField.vue";
import TextAreaV3 from "../TextAreaV3.vue";
import { PhWarning } from "@phosphor-icons/vue";
import { computed, ref, watch } from "vue";
import { getCurrentInstance, reactive } from "vue";
import { getNode } from "@formkit/core";
import { useFormKitNodeById } from "@formkit/vue";
import { useFormValues } from "../../../composables/useDpiFormValues";

let URIList = ref([]);
let instance = getCurrentInstance().appContext.app.config.globalProperties.$env;

const props = defineProps({
  context: Object,
  newValues: Object,
});

const { formValues } = useFormValues()

const model = defineModel({ default: {
  title: '',
  description: '',
  modified: '',
  publisherName: '',
  publisherMail: '',
  publisherWebsite: '',
  contactPointName: '',
  contactPointMail: '',
  contactPointPhone: '',
  // Error state properties
  showErrorTitle: false,
  titleErrorMessage: '',
  showErrorDescription: false,
  descriptionErrorMessage: '',
  showErrorPublisherName: false,
  publisherNameErrorMessage: '',
  showErrorPublisherMail: false,
  publisherMailErrorMessage: '',
  showErrorPublisherWebsite: false,
  publisherWebsiteErrorMessage: '',
  showErrorContactPointName: false,
  contactPointNameErrorMessage: '',
  showErrorContactPointMail: false,
  contactPointMailErrorMessage: '',
  showErrorContactPointPhone: false,
  contactPointPhoneErrorMessage: '',
  modified: '',
  showErrorModified: false,
  modifiedErrorMessage: '',
}})

// Validation functions
const isValidEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email.trim());
};

const validatePhone = (value) => {
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
};

const isValidContactName = (name) => {
  if (!name || !name.trim()) return false;
  return name.trim().length >= 2;
};

const isValidUrl = (url) => {
  try {
    const cleanUrl = url.trim();
    // Add protocol if missing
    const urlToTest = cleanUrl.startsWith('http://') || cleanUrl.startsWith('https://') 
      ? cleanUrl 
      : `https://${cleanUrl}`;
    new URL(urlToTest);
    return true;
  } catch {
    return false;
  }
};

const isValidDateRange = (dateString) => {
  if (!dateString) return false;
  
  const inputDate = new Date(dateString);
  if (isNaN(inputDate.getTime())) return false;

  const year = inputDate.getFullYear();
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const compareDate = new Date(inputDate);
  compareDate.setHours(0, 0, 0, 0);

  return year >= 1950 && compareDate <= today;
};

const isValidName = (name) => {
  if (!name || !name.trim()) return false;
  // Check if name contains at least one alphanumeric character
  const hasAlphanumeric = /[a-zA-Z0-9]/.test(name.trim());
  return hasAlphanumeric;
};

const isRequiredFieldValid = (value) => {
  return value && value.trim().length > 0;
};

// Individual field validation functions
const validateField = (fieldName, value) => {
  switch (fieldName) {
    case 'title':
      if (!isValidName(value)) {
        model.value.showErrorTitle = true;
        model.value.titleErrorMessage = 'Bitte geben Sie einen Titel ein, bevor Sie fortfahren.';
        return false;
      } else {
        model.value.showErrorTitle = false;
        model.value.titleErrorMessage = '';
        return true;
      }

    case 'description':
      if (!isRequiredFieldValid(value)) {
        model.value.showErrorDescription = true;
        model.value.descriptionErrorMessage = 'Bitte geben Sie eine Beschreibung ein, bevor Sie fortfahren.';
        return false;
      } else {
        model.value.showErrorDescription = false;
        model.value.descriptionErrorMessage = '';
        return true;
      }

    case 'modified':
      if (!value) {
        model.value.showErrorModified = true;
        model.value.modifiedErrorMessage = 'Bitte geben Sie ein Aktualisierungsdatum ein.';
        return false;
      }
      const inputDate = new Date(value);
      const today = new Date();
      const inputMidnight = new Date(inputDate.getFullYear(), inputDate.getMonth(), inputDate.getDate());
      const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());

      if (isNaN(inputDate.getTime())) {
        model.value.showErrorModified = true;
        model.value.modifiedErrorMessage = 'Bitte geben Sie ein gültiges Datum ein.';
        return false;
      } else if (inputDate.getFullYear() < 1950) {
        model.value.showErrorModified = true;
        model.value.modifiedErrorMessage = 'Das Jahr darf nicht vor 1950 liegen.';
        return false;
      } else if (inputDate > today) {
        model.value.showErrorModified = true;
        model.value.modifiedErrorMessage = 'Das Datum darf nicht in der Zukunft liegen.';
        return false;
      } else {
        model.value.showErrorModified = false;
        model.value.modifiedErrorMessage = '';
        return true;
      }

    case 'publisherName':
      if (!isValidName(value)) {
        model.value.showErrorPublisherName = true;
        model.value.publisherNameErrorMessage = 'Bitte geben Sie einen gültigen Organisationsnamen ein.';
        return false;
      } else {
        model.value.showErrorPublisherName = false;
        model.value.publisherNameErrorMessage = '';
        return true;
      }

    case 'publisherMail':
      if (!value || !value.trim() || !isValidEmail(value)) {
        model.value.showErrorPublisherMail = true;
        model.value.publisherMailErrorMessage = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        return false;
      } else {
        model.value.showErrorPublisherMail = false;
        model.value.publisherMailErrorMessage = '';
        return true;
      }

    case 'publisherWebsite':
      if (!value || !value.trim() || !isValidUrl(value)) {
        model.value.showErrorPublisherWebsite = true;
        model.value.publisherWebsiteErrorMessage = 'Bitte geben Sie eine gültige URL ein.';
        return false;
      } else {
        model.value.showErrorPublisherWebsite = false;
        model.value.publisherWebsiteErrorMessage = '';
        return true;
      }

    case 'contactPointName':
      if (!isValidContactName(value)) {
        model.value.showErrorContactPointName = true;
        model.value.contactPointNameErrorMessage = 'Bitte geben Sie einen gültigen Namen ein (mindestens 2 Zeichen).';
        return false;
      } else {
        model.value.showErrorContactPointName = false;
        model.value.contactPointNameErrorMessage = '';
        return true;
      }

    case 'contactPointMail':
      if (!value || !value.trim() || !isValidEmail(value)) {
        model.value.showErrorContactPointMail = true;
        model.value.contactPointMailErrorMessage = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        return false;
      } else {
        model.value.showErrorContactPointMail = false;
        model.value.contactPointMailErrorMessage = '';
        return true;
      }

    case 'contactPointPhone':
      if (value && value.trim() && !validatePhone(value)) {
        model.value.showErrorContactPointPhone = true;
        model.value.contactPointPhoneErrorMessage = 'Bitte geben Sie eine gültige Telefonnummer ein.';
        return false;
      } else {
        model.value.showErrorContactPointPhone = false;
        model.value.contactPointPhoneErrorMessage = '';
        return true;
      }

    default:
      return true;
  }
};

// Overall validation state
const isValid = computed(() => {
  const titleValid = isValidName(model.value.title);
  const descriptionValid = isRequiredFieldValid(model.value.description);
  const modifiedValid = isValidDateRange(model.value.modified);
  const publisherNameValid = isValidName(model.value.publisherName);
  const publisherMailValid = model.value.publisherMail?.trim() && isValidEmail(model.value.publisherMail);
  const publisherWebsiteValid = model.value.publisherWebsite?.trim() && isValidUrl(model.value.publisherWebsite);
  const contactPointNameValid = isValidContactName(model.value.contactPointName);
  const contactPointMailValid = model.value.contactPointMail?.trim() && isValidEmail(model.value.contactPointMail);
  const contactPointPhoneValid = !model.value.contactPointPhone?.trim() || validatePhone(model.value.contactPointPhone);
  
  return titleValid && descriptionValid && publisherNameValid && publisherMailValid && publisherWebsiteValid && contactPointNameValid && contactPointMailValid && contactPointPhoneValid && modifiedValid;
});

// Watch for changes and validate fields
watch(() => model.value.title, (newValue) => validateField('title', newValue));
watch(() => model.value.description, (newValue) => validateField('description', newValue));
watch(() => model.value.publisherName, (newValue) => validateField('publisherName', newValue));
watch(() => model.value.publisherMail, (newValue) => validateField('publisherMail', newValue));
watch(() => model.value.publisherWebsite, (newValue) => validateField('publisherWebsite', newValue));
watch(() => model.value.contactPointMail, (newValue) => validateField('contactPointMail', newValue));
watch(() => model.value.contactPointName, (newValue) => validateField('contactPointName', newValue));
watch(() => model.value.contactPointMail, (newValue) => validateField('contactPointMail', newValue));
watch(() => model.value.contactPointPhone, (newValue) => validateField('contactPointPhone', newValue));
watch(() => model.value.modified, (newValue) => validateField('modified', newValue));

// For backward compatibility, keep the validation errors computed
const validationErrors = computed(() => {
  return {
    title: !isValidName(model.value.title),
    description: !isRequiredFieldValid(model.value.description),
    publisherName: !isValidName(model.value.publisherName),
    publisherMail: !model.value.publisherMail?.trim() || !isValidEmail(model.value.publisherMail),
    publisherWebsite: !model.value.publisherWebsite?.trim() || !isValidUrl(model.value.publisherWebsite),
    contactPointName: !isValidContactName(model.value.contactPointName), // Now always validated
    contactPointMail: !model.value.contactPointMail?.trim() || !isValidEmail(model.value.contactPointMail),  
  };
});

// Get validation error messages
const getValidationErrorMessages = () => {
  const messages = [];
  
  if (model.value.showErrorTitle) messages.push(model.value.titleErrorMessage);
  if (model.value.showErrorDescription) messages.push(model.value.descriptionErrorMessage);
  if (model.value.showErrorPublisherName) messages.push(model.value.publisherNameErrorMessage);
  if (model.value.showErrorPublisherMail) messages.push(model.value.publisherMailErrorMessage);
  if (model.value.showErrorPublisherWebsite) messages.push(model.value.publisherWebsiteErrorMessage);
  if (model.value.showErrorContactPointName) messages.push(model.value.contactPointNameErrorMessage);
  if (model.value.showErrorContactPointMail) messages.push(model.value.contactPointMailErrorMessage);
  if (model.value.showErrorContactPointPhone) messages.push(model.value.contactPointPhoneErrorMessage);
  if (model.value.showErrorModified) messages.push(model.value.modifiedErrorMessage);
  
  return messages.length > 0 ? messages : ['Bitte füllen Sie alle erforderlichen Felder korrekt aus.'];
};

// Force reactivity by watching the model and triggering updates
watch(() => model.value, () => {
  // This helps ensure the parent component gets updated validation state
}, { deep: true });

// Expose validation functions to parent
defineExpose({
  isValid,
  validationErrors,
  getValidationErrorMessages,
  validateField
});
</script>

<style scoped>
.topSpacing {
  margin-top: var(--Spacing-5, 32px);
}
h5 {
}

.dpiV3_innerRapModalWrapper__section {
  width: 100%;
}

.dpiV3_field_error_text {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: center;
  justify-content: end;
  align-self: stretch;
  color: var(--text-error, #a9242c);
  font-family: var(--font-family-secondary, Inter);
  font-size: var(--copy-small-regular-font-size, 15px);
  font-style: normal;
  font-weight: var(--copy-small-regular-font-weight, 400);
  line-height: var(--copy-small-regular-line-height, 24px);
  margin-top: 4px;
  gap: 4px;
}

.dpiV3_field_error_text svg {
  flex-shrink: 0;
  margin-top: 2px;
}
</style>
