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
      ]?.['dct:temporal'] || timesList.value"
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
            :showError="dateErrors[index]?.startDate && formValues['Covering']['dcat:temporalResolution'].isValid === false"
          >
          </InputField>
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
            :showError="dateErrors[index]?.endDate && formValues['Covering']['dcat:temporalResolution'].isValid === false"
          >
          </InputField>
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
        </div>
      </div>
      <div class="validation-error" v-if="dateErrors[index]?.message && formValues['Covering']['dcat:temporalResolution'].isValid === false">
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular"
          >
        {{ dateErrors[index].message }}</span>
      </div>
      <div class="dpiV3_secondRowTemporal">
        <!-- Key: Using key to force re-rendering of the checkbox when reset -->
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
        <!-- Show trash button for all items when there are multiple items -->
        <ButtonV3
          v-if="timesList.length > 1"
          :buttonText="t('message.dataupload.menu.delete')"
          size="small"
          iconStart="trash"
          variant="tertiary"
          @click="removeIndex(index)"
        />
        <!-- Show reset button only when there's just one item -->
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
import { ref, watch } from "vue";
import { PhWarning } from "@phosphor-icons/vue";
import { useI18n } from "vue-i18n";
import { getNode } from "@formkit/core";
import { useFormValues } from "../composables/useDpiFormValues";
import { useEditModeInfo } from "../composables/useDpiEditMode";

const { isEditMode } = useEditModeInfo();
const { formValues } = useFormValues();
const { t } = useI18n();
// Data model for checkbox states
const checkboxStates = ref([false]);
// Counter to force re-rendering of checkboxes
const checkboxResetCounter = ref([0]);
let timesList = ref();
let dateErrors = ref([{ startDate: false, endDate: false, message: "" }]);

const currentIndex = ref();
let dynamicButtonText = ref(t("message.dataupload.menu.reset"));

const props = defineProps({
  context: Object,
  inRap: Boolean,
});

if (isEditMode.value) {
  // need to make sure that if this Component is in the RAP Page, the correct data gets loaded
  if (formValues.value["Covering"]["dcat:temporalResolution"] !== undefined) {
    timesList.value =
      formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"] ||
      [];
   
  } else {
   
    // formValues.value["Covering"]["dcat:temporalResolution"] = [
    //   {
    //     isValid: true,
    //     type: "dct:PeriodOfTime",
    //     "dct:temporal": [
    //       {
    //         dataType: "date",
    //         "dcat:startDate": "",
    //         "dcat:endDate": "",
    //       },
    //     ],
    //   },
    // ];
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
      }
    ]
  }

  // Initialize checkbox states based on the loaded data
  checkboxStates.value = timesList?.value.map(
    (item) => item.dataType === "dateTime"
  );
  // Initialize reset counters
  checkboxResetCounter.value = new Array(timesList?.value.length).fill(0);
} else {
  timesList.value = [{ id: 0, "dcat:startDate": "", "dcat:endDate": "" }];
  checkboxResetCounter.value = [0];
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

if (!isEditMode.value) {
  (timesList.value = [
    {
      dataType: "date",
      "dcat:startDate": "",
      "dcat:endDate": "",
    },
  ]),
    (formValues.value["Covering"]["dcat:temporalResolution"] = {
      isValid: true,
      type: "dct:PeriodOfTime",
      "dct:temporal": [
        {
          dataType: "date",
          "dcat:startDate": "",
          "dcat:endDate": "",
        },
      ],
    });
}

// Function to get the checkbox state (for v-show directives)
const getCheckboxState = (index) => {
  return checkboxStates.value[index] === true;
};

// Watcher for dynamic text updates
watch(
  () => t("message.dataupload.menu.reset"),
  (newText) => {
    dynamicButtonText.value = newText;
  }
);

// Add a new time period
const addNewItem = () => {
  // Find the max ID currently in use
  const maxId = timesList.value.reduce(
    (max, item) => Math.max(max, item.id || 0),
    0
  );
  // Add new item with unique ID
    formValues.value["Covering"]["dcat:temporalResolution"]?.[
      "dct:temporal"
    ].push({ id: maxId + 1, dataType: "date" });

  timesList.value.push({ id: maxId + 1, dataType: "date" });

  dynamicButtonText.value = t("message.dataupload.menu.delete");
  initNewIndex();
  validateDates(timesList.value.length - 1);
};

// Initialize a new index in the temporal array
const initNewIndex = () => {
  // Cache list and add indices
  //   let cacheList = arr.value[0];
  //   cacheList.push({
  //     dataType: "date",
  //     "dcat:startDate": "",
  //     "dcat:endDate": "",
  //   });
  //   arr.value[0] = cacheList;

  // Initialize a new checkbox state
  checkboxStates.value.push(false);

  // Initialize a new reset counter
  checkboxResetCounter.value.push(0);

  // Initialize a new error state
  dateErrors.value.push({ startDate: false, endDate: false, message: "" });
  //   formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"] = arr;
  //   props.context.node.input(arr);
};

// Handle checkbox state changes
const handleCheckboxChange = (event, i) => {
  // Extract the checked value
  const checked = event.target.checked;
  checkboxStates.value[i] = checked;

  // Update data type based on checkbox state
  if (checkboxStates.value[i]) {
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][i][
      "dataType"
    ] = "dateTime";
  } else {
    formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][i][
      "dataType"
    ] = "date";
  }

  // Re-validate after changing the data type
  validateDates(i);
};

// Validate date ranges and field completeness
const validateDates = (index) => {
  const item = formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"][index];
  const startDate = item["dcat:startDate"];
  const endDate = item["dcat:endDate"];
  const startTime = item["startTime"];
  const endTime = item["endTime"];
  const isDateTime = item.dataType === "dateTime";

  // Reset error states
  dateErrors.value[index] = { startDate: false, endDate: false, message: "" };

  // Set initial validity
  let isValid = true;

  // Check if any field has a value
  const hasAnyValue = startDate || endDate || startTime || endTime;

  if (hasAnyValue) {
    // If any field is filled, all required fields must be filled
    const missingFields = [];

    if (!startDate) missingFields.push("Aktualisierungsdatum");
    if (!endDate) missingFields.push("Enddatum");
    
    if (isDateTime) {
      if (!startTime) missingFields.push("Startzeit");
      if (!endTime) missingFields.push("Endzeit");
    }

    if (missingFields.length > 0) {
      dateErrors.value[index] = {
        startDate: !startDate,
        endDate: !endDate,
        message: `Bitte geben Sie eine ${missingFields.join(" und eine ")} an.`,
      };
      isValid = 'unset';
    } else {
      // If all required fields are filled, validate date range
      const start = new Date(startDate);
      const end = new Date(endDate);

      if (end < start) {
        dateErrors.value[index] = {
          startDate: true,
          endDate: true,
          message: "Enddatum muss nach dem Aktualisierungsdatum liegen.",
        };
        isValid = 'unset';
      }
    }
  }

  // Update isValid in the data model
  arr.value[0].isValid = isValid;
  formValues.value["Covering"]["dcat:temporalResolution"].isValid = isValid;
  return isValid;
};

const handleInput = (dateItem, index, key, currentItem) => {
  // Update time properties
  //   if (key === "endTime" || key === "startTime") {
  //     if (key === "endTime") {
  //       arr.value[0]["dct:temporal"][index]["endTime"] = dateItem;
  //     }
  //     if (key === "startTime") {
  //       arr.value[0]["dct:temporal"][index]["startTime"] = dateItem;
  //     }
  //   } else {

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

  // arr.value[0]["dct:temporal"][index][key] = dateItem;
  //   }

  // Validate dates after input
  const isValid = validateDates(index);
};

// Reset all fields for a given index
const resetFields = (index) => {
  if (timesList.value[index]) {
    // Reset date fields
    timesList.value[index]["dcat:startDate"] = "";
    timesList.value[index]["dcat:endDate"] = "";

    // Reset time fields if they exist
    if ("startTime" in timesList.value[index]) {
      timesList.value[index]["startTime"] = "";
    }
    if ("endTime" in timesList.value[index]) {
      timesList.value[index]["endTime"] = "";
    }

    // Reset the data model
    arr.value[0]["dct:temporal"][index] = {
      dataType: "date",
      "dcat:startDate": "",
      "dcat:endDate": "",
    };

    // Reset checkbox state
    checkboxStates.value[index] = false;

    // Increment the reset counter to force checkbox re-rendering
    checkboxResetCounter.value[index]++;

    // Reset validation errors
    dateErrors.value[index] = { startDate: false, endDate: false, message: "" };

    // Update the node
    if (props.inRap) {
      getNode("Covering").value[Object.keys(getNode("Covering").value)[1]] =
        arr;
    } else {
    console.log(arr);
    
      formValues.value.Covering["dcat:temporalResolution"] = arr.value[0]
      // props.context.node.input(arr);
    }
    validateDates(index);
    console.log(timesList);
    
  }
};

// Remove an index from the temporal array
const removeIndex = (index) => {
  // Use the object's ID to remove it

  formValues.value["Covering"]["dcat:temporalResolution"][
    "dct:temporal"
  ].splice(index, 1);
timesList.value.splice(index, 1);
  // Also remove the corresponding checkbox state
  checkboxStates.value.splice(index, 1);

  // Remove corresponding reset counter
  checkboxResetCounter.value.splice(index, 1);

  // Remove corresponding error state
  dateErrors.value.splice(index, 1);

  // Update button text if only one item remains
  if (formValues.value["Covering"]["dcat:temporalResolution"][
    "dct:temporal"
  ].length === 1) {
    dynamicButtonText.value = t("message.dataupload.menu.reset");
  }
  formValues.value["Covering"]["dcat:temporalResolution"]["dct:temporal"].forEach((_, idx) => {
    validateDates(idx);
  });  
};
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
  display: flex;
  gap: 5px;
  justify-content: end;
  width: 100%;
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