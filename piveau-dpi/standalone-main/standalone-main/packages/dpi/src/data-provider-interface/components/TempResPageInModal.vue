<template>
  <div class="dpiV3InnerComponentWrap" :class="{ inRapModal: inRap }">
    <h4 v-if="!inRap">
      {{ $t("message.dataupload.datasets.dcat:temporalResolution.title") }}
    </h4>
    <div v-if="!inRap" class="copy-large-regular">
      {{
        $t("message.dataupload.datasets.dcat:temporalResolution.description")
      }}
    </div>
    <div v-if="inRap">
      <span class="dpiV3_label"
        >{{ $t("message.metadata.temporal") }} (optional)</span
      >
    </div>
    <div
      class="dpiV3AutoCompleteWrap"
      v-for="(item, index) in formValues['Covering'][
        'dcat:temporalResolution'
      ]?.['dct:temporal'] || initializedObject"
      :class="{ marginRap: inRap && index + 1 < timesList.length }"
    >
      <div class="dpiV3_firstRow" v-if="item != ''">
        <div class="dpiV3_firstRow_inner">
          <InputField
            @input="
              handleInput($event.target.value, index, 'dcat:startDate', item)
            "
            :defaultInput="true"
            :addOnText="false"
            :eraseable="false"
            :datePicker="true"
            :infoIcon="false"
            value="TT/MM/JJJJ"
            label="Von"
            :preIcon="false"
            inputFieldSize="large"
            :initialHintText="false"
            :showEndIcon="false"
            inputType="date"
            v-model="item['dcat:startDate']"
            :showError="fieldErrors[index]?.startDate"
          >
          </InputField>
          <div
            v-if="fieldErrors[index]?.startDateMessage"
            class="dpiV3_errorMessage"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              fieldErrors[index].startDateMessage
            }}</span>
          </div>
          <InputField
            v-show="getCheckboxState(index)"
            @input="handleInput($event.target.value, index, 'startTime', item)"
            :defaultInput="true"
            :addOnText="false"
            :eraseable="false"
            :timePicker="true"
            inputType="time"
            :infoIcon="false"
            value="00:00"
            :label="false"
            :preIcon="false"
            inputFieldSize="large"
            :initialHintText="false"
            :showEndIcon="false"
            v-model="item['startTime']"
          >
          </InputField>
          <div
            v-if="
              getCheckboxState(index) && fieldErrors[index]?.startTimeMessage
            "
            class="dpiV3_errorMessage"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              fieldErrors[index].startTimeMessage
            }}</span>
          </div>
        </div>
        <div class="dpiV3_firstRow_inner">
          <InputField
            @input="
              handleInput($event.target.value, index, 'dcat:endDate', item)
            "
            :defaultInput="true"
            :addOnText="false"
            :eraseable="false"
            :datePicker="true"
            :infoIcon="false"
            value="TT/MM/JJJJ"
            label="Bis"
            :preIcon="false"
            inputFieldSize="large"
            :initialHintText="false"
            :showEndIcon="false"
            inputType="date"
            v-model="item['dcat:endDate']"
            :showError="fieldErrors[index]?.endDate"
          >
          </InputField>
          <div
            v-if="fieldErrors[index]?.endDateMessage"
            class="dpiV3_errorMessage"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              fieldErrors[index].endDateMessage
            }}</span>
          </div>
          <InputField
            v-show="getCheckboxState(index)"
            @input="handleInput($event.target.value, index, 'endTime', item)"
            :defaultInput="true"
            :addOnText="false"
            :eraseable="false"
            :timePicker="true"
            inputType="time"
            :infoIcon="false"
            value="00:00"
            :label="false"
            :preIcon="false"
            inputFieldSize="large"
            :initialHintText="false"
            :showEndIcon="false"
            v-model="item['endTime']"
          >
          </InputField>
          <div
            v-if="getCheckboxState(index) && fieldErrors[index]?.endTimeMessage"
            class="dpiV3_errorMessage"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              fieldErrors[index].endTimeMessage
            }}</span>
          </div>
        </div>
      </div>
      <div class="validation-error" v-if="dateErrors[index]?.message">
        <PhWarning :size="16" weight="fill" />
       <span class="copy-mini-regular">{{ dateErrors[index].message }}</span> 
      </div>
      <div class="dpiV3_secondRowTemporal">
        <div :key="`checkbox-${index}-${checkboxResetCounter[index]}`">
          <CheckboxV3
            :type="'checkbox'"
            :state="getCheckboxState(index) ? 'checked' : ''"
            :text="{
              label: $t(
                'message.dataupload.datasets.dcat:temporalResolution.input.checkbox.label'
              ),
            }"
            :data="[]"
            @change="handleCheckboxChange($event, index)"
          />
        </div>
        <ButtonV3
          v-if="timesList.length > 1"
          :buttonText="t('message.dataupload.menu.delete')"
          size="small"
          iconStart="trash"
          variant="tertiary"
          @click="removeIndex(index)"
        />
        <ButtonV3
          v-else
          :buttonText="t('message.dataupload.menu.reset')"
          size="small"
          iconStart="reset"
          variant="tertiary"
          @click="resetFields(index)"
        >
        </ButtonV3>
      </div>
    </div>
    <div class="dpiV3_tempAddMore" :class="{ inRapAddMore: inRap }">
      <ButtonV3
        :buttonText="
          $t('message.dataupload.datasets.dcat:temporalResolution.addMore')
        "
        size="medium"
        iconStart="plus"
        variant="tertiary"
        @click="addNewItem()"
      />
    </div>
  </div>
</template>

<script setup>
import InputField from "../HappyFlowComponents/ui/InputField.vue";
import CheckboxV3 from "../HappyFlowComponents/ui/CheckboxV3.vue";
import ButtonV3 from "../HappyFlowComponents/ui/ButtonV3.vue";
import { PhWarning } from "@phosphor-icons/vue";
import { ref, watch, reactive } from "vue";
import { useI18n } from "vue-i18n";
import { getNode } from "@formkit/core";
import { useFormValues } from "../composables/useDpiFormValues";
import { useEditModeInfo } from "../composables/useDpiEditMode";

const { isEditMode } = useEditModeInfo();
const { formValues } = useFormValues();
const { t } = useI18n();

const checkboxStates = ref([false]);
const checkboxResetCounter = ref([0]);
let timesList = ref();
let dateErrors = ref([{ startDate: false, endDate: false, message: "" }]);
let fieldErrors = reactive([
  {
    startDate: false,
    startDateMessage: "",
    endDate: false,
    endDateMessage: "",
    startTime: false,
    startTimeMessage: "",
    endTime: false,
    endTimeMessage: "",
  },
]);
const validationTimeouts = reactive({});

const currentIndex = ref();
let dynamicButtonText = ref(t("message.dataupload.menu.reset"));

const props = defineProps({
  context: Object,
  inRap: Boolean,
});

if (isEditMode.value) {
  if (formValues.value["Covering"]["dcat:temporalResolution"] !== undefined) {
    timesList.value =
      formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"] ||
      [];
  } else {
    timesList.value = [
      {
        isValid: true,
        type: "dct:PeriodOfTime",
        "dct:temporal": [
          {
            dataType: "date",
            "dcat:startDate": "",
            "dcat:endDate": "",
          },
        ],
      },
    ];
  }

  checkboxStates.value = timesList?.value.map(
    (item) => item.dataType === "dateTime"
  );
  checkboxResetCounter.value = new Array(timesList?.value.length).fill(0);
  
  // Initialize field errors for each item
  fieldErrors.length = 0;
  dateErrors.value = [];
  timesList.value.forEach(() => {
    fieldErrors.push({
      startDate: false,
      startDateMessage: "",
      endDate: false,
      endDateMessage: "",
      startTime: false,
      startTimeMessage: "",
      endTime: false,
      endTimeMessage: "",
    });
    dateErrors.value.push({ startDate: false, endDate: false, message: "" });

  });
} else {
  timesList.value =
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"] ||
    [];
  checkboxStates.value = timesList?.value.map(
    (item) => item.dataType === "dateTime"
  );
}

const arr = ref([
  {
    isValid: true,
    type: "dct:PeriodOfTime",
    "dct:temporal": [
      { dataType: "date", "dcat:startDate": "", "dcat:endDate": "" },
    ],
  },
]);

const getCheckboxState = (index) => {
  return checkboxStates.value[index] === true;
};

watch(
  () => t("message.dataupload.menu.reset"),
  (newText) => {
    dynamicButtonText.value = newText;
  }
);

// Check if any field in the item is filled
const hasAnyFieldFilled = (index) => {
  const item =
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][
      index
    ];
  if (!item) return false;

  const hasStartDate = item["dcat:startDate"] && item["dcat:startDate"].trim() !== "";
  const hasEndDate = item["dcat:endDate"] && item["dcat:endDate"].trim() !== "";
  const hasStartTime =
    getCheckboxState(index) &&
    item["startTime"] &&
    item["startTime"].trim() !== "";
  const hasEndTime =
    getCheckboxState(index) && item["endTime"] && item["endTime"].trim() !== "";

  return hasStartDate || hasEndDate || hasStartTime || hasEndTime;
};

// Validate all fields for a specific index
const validateFields = (index) => {
  const item =
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][
      index
    ];
  if (!item) return true;

  // Initialize error state for this index if it doesn't exist
  if (!fieldErrors[index]) {
    fieldErrors[index] = {
      startDate: false,
      startDateMessage: "",
      endDate: false,
      endDateMessage: "",
      startTime: false,
      startTimeMessage: "",
      endTime: false,
      endTimeMessage: "",
    };
  }

  // Reset errors
  fieldErrors[index] = {
    startDate: false,
    startDateMessage: "",
    endDate: false,
    endDateMessage: "",
    startTime: false,
    startTimeMessage: "",
    endTime: false,
    endTimeMessage: "",
  };

  const anyFieldFilled = hasAnyFieldFilled(index);
  let hasErrors = false;

  // If any field is filled, all required fields must be filled
  if (anyFieldFilled) {
    // Start date is required
    if (!item["dcat:startDate"] || item["dcat:startDate"].trim() === "") {
      fieldErrors[index].startDate = true;
      fieldErrors[index].startDateMessage = "Startdatum ist erforderlich";
      hasErrors = true;
    }

    // End date is required
    if (!item["dcat:endDate"] || item["dcat:endDate"].trim() === "") {
      fieldErrors[index].endDate = true;
      fieldErrors[index].endDateMessage = "Enddatum ist erforderlich";
      hasErrors = true;
    }

    // If checkbox is checked, time fields are also required
    if (getCheckboxState(index)) {
      if (!item["startTime"] || item["startTime"].trim() === "") {
        fieldErrors[index].startTime = true;
        fieldErrors[index].startTimeMessage = "Startzeit ist erforderlich";
        hasErrors = true;
      }

      if (!item["endTime"] || item["endTime"].trim() === "") {
        fieldErrors[index].endTime = true;
        fieldErrors[index].endTimeMessage = "Endzeit ist erforderlich";
        hasErrors = true;
      }
    }
  }

  return !hasErrors;
};

// Debounced validation
const debouncedValidation = (index) => {
  if (validationTimeouts[index]) {
    clearTimeout(validationTimeouts[index]);
  }

  validationTimeouts[index] = setTimeout(() => {
    validateFields(index);
  }, 200);
};

// Validate all items - returns true if there are errors
const validateAllItems = () => {
  let hasErrors = false;
  
  if (formValues.value["Covering"]["dcat:temporalResolution"]?.["dct:temporal"]) {
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"].forEach(
      (item, index) => {
        // Validate field completeness
        if (!validateFields(index)) {
          hasErrors = true;
        }
        
        // Also validate date ranges
        if (!validateDates(index)) {
          hasErrors = true;
        }
      }
    );
  }
  
  return hasErrors;
};

const addNewItem = () => {
  // Validate all items before adding a new one
  if (validateAllItems()) {
    return;
  }

  const maxId = timesList.value.reduce(
    (max, item) => Math.max(max, item.id || 0),
    0
  );
  timesList.value.push({ id: maxId + 1, dataType: "date" });

  dynamicButtonText.value = t("message.dataupload.menu.delete");
  initNewIndex();
};

const initNewIndex = () => {
  checkboxStates.value.push(false);
  checkboxResetCounter.value.push(0);
  fieldErrors.push({
    startDate: false,
    startDateMessage: "",
    endDate: false,
    endDateMessage: "",
    startTime: false,
    startTimeMessage: "",
    endTime: false,
    endTimeMessage: "",
  });
  dateErrors.value.push({ startDate: false, endDate: false, message: "" });

};

const handleCheckboxChange = (event, i) => {
  const checked = event.target.checked;
  checkboxStates.value[i] = checked;

  if (checkboxStates.value[i]) {
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][i][
      "dataType"
    ] = "dateTime";
  } else {
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][i][
      "dataType"
    ] = "date";
    // Clear time field errors when unchecking
    if (fieldErrors[i]) {
      fieldErrors[i].startTimeMessage = "";
      fieldErrors[i].endTimeMessage = "";
    }
  }

  // Revalidate after checkbox change
  debouncedValidation(i);
};

const validateDates = (index) => {
  while (dateErrors.value.length <= index) {
    dateErrors.value.push({ startDate: false, endDate: false, message: "" });
  }
  const startDate =
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][
      index
    ]["dcat:startDate"];
  const endDate =
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][
      index
    ]["dcat:endDate"];

  // Initialize error state if it doesn't exist
  if (!dateErrors.value[index]) {
    dateErrors.value[index] = { startDate: false, endDate: false, message: "" };
  }

  // Reset error state
  dateErrors.value[index] = { startDate: false, endDate: false, message: "" };

  let isValid = true;

  if (startDate && endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);

    if (end < start) {
      dateErrors.value[index] = {
        startDate: true,
        endDate: true,
        message: "Enddatum muss nach dem Aktualisierungsdatum liegen",
      };
      isValid = false;
    }
  }

  arr.value[0].isValid = isValid;

  return isValid;
};

const handleInput = (dateItem, index, key, currentItem) => {
  if (formValues.value.Covering["dcat:temporalResolution"] !== undefined) {
    formValues.value.Covering["dcat:temporalResolution"]["dct:temporal"][index][
      key
    ] = dateItem;
  } else {
    formValues.value.Covering["dcat:temporalResolution"] = {
      isValid: true,
      type: "dct:PeriodOfTime",
      "dct:temporal": [
        {
          dataType: "date",
          "dcat:startDate": "",
          "dcat:endDate": "",
        },
      ],
    };

    formValues.value.Covering["dcat:temporalResolution"]["dct:temporal"][index][
      key
    ] = dateItem;
  }

  validateDates(index);
  
  // Trigger debounced validation for field completion
  debouncedValidation(index);
};

const resetFields = (index) => {
  const item = formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][index];

  if (item) {
    // Directly clear the values
    item["dcat:startDate"] = "";
    item["dcat:endDate"] = "";
    item["startTime"] = "";
    item["endTime"] = "";
    item["dataType"] = "date";

    // Reset UI-specific states
    checkboxStates.value[index] = false;
    checkboxResetCounter.value[index]++;

    // Clear error objects so the UI looks "clean"
    dateErrors.value[index] = { startDate: false, endDate: false, message: "" };
    fieldErrors[index] = {
      startDate: false,
      startDateMessage: "",
      endDate: false,
      endDateMessage: "",
      startTime: false,
      startTimeMessage: "",
      endTime: false,
      endTimeMessage: "",
    };
  }
};

const removeIndex = (index) => {
  formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"].splice(
    index,
    1
  );

  timesList.value =
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"];

  checkboxStates.value.splice(index, 1);
  checkboxResetCounter.value.splice(index, 1);
  dateErrors.value.splice(index, 1);
  fieldErrors.splice(index, 1);

  if (props.inRap) {
  } else {
    props.context.node.input(arr);
  }

  if (timesList.value.length === 1) {
    dynamicButtonText.value = t("message.dataupload.menu.reset");
  }
};

// Exposing validation method for parent component
defineExpose({
  validateAllItems,
});
</script>

<style scoped>
.dpiV3_firstRow {
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-4, 24px);
  align-self: stretch;
}

.dpiV3_firstRow_inner {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: var(--Spacing-2, 8px);
}

.validation-error {
  color: var(--text-error, #a9242c);
  font-size: var(--copy-small-regular-font-size, 15px);
  margin-top: 4px;
}

.dpiV3_errorMessage {
  display: flex;
  align-items: center;
  gap: 4px;
  color: var(--text-error, #a9242c);
  margin-top: 4px;
  margin-bottom: 8px;
  font-size: var(--copy-small-regular-font-size, 15px);
}

.dpiV3_errorMessage svg {
  color: var(--text-error, #a9242c);
  flex-shrink: 0;
}

.dpiV3_secondRowTemporal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  align-self: stretch;
}

.dpiV3_tempAddMore {
  width: 100%;
  display: flex;
  justify-content: end;
}

.dpiV3_input-field {
  width: unset;
}

.dpiV3AutoCompleteWrap {
  gap: var(--Spacing-5, 32px);
}

.inRapModal {
  gap: 0;
}

.inRapAddMore {
  margin-top: var(--Spacing-3, 16px);
}

.dpiV3_Label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.dpiV3_label {
  color: var(--neutral-80, #3d4952);
  font-family: var(--font-family-secondary, Inter);
  font-size: var(--copy-small-regular-font-size, 15px);
  font-style: normal;
  font-weight: var(--copy-small-regular-font-weight, 400);
  line-height: var(--copy-small-regular-line-height, 24px);
  margin-bottom: var(--Spacing-1, 4px);
}

.marginRap {
  margin-bottom: var(--Spacing-4, 24px);
}
</style>