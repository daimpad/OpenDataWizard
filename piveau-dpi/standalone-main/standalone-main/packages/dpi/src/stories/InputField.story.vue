<script setup lang="ts">
import InputField from '../data-provider-interface/HappyFlowComponents/ui/InputField.vue'
</script>

<template>
  <Story title="Base Components/InputField">
    <!-- Basic Input Field Types -->
    <Variant title="Basic Input Fields" icon="carbon:text-input">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <InputField
            label="Standard Input"
            placeholder="Enter text here"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Input with Info Icon"
            placeholder="Enter text here"
            tooltip-text="This is helpful information"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="true"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Input with Search Icon"
            placeholder="Search..."
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
            :pre-icon="true"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Input without Pre Icon"
            placeholder="Enter text here"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
            :pre-icon="false"
          />
        </div>
      </div>
    </Variant>

    <!-- Input Field Sizes -->
    <Variant title="Input Field Sizes" icon="carbon:resize">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <InputField
            label="Medium Size (Default)"
            placeholder="Medium input field"
            input-field-size="medium"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Large Size"
            placeholder="Large input field"
            input-field-size="large"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
      </div>
    </Variant>

    <!-- Add-on Text Variations -->
    <Variant title="Add-on Text Variations" icon="carbon:text-link">
      <div class="d-flex flex-column">
        <h4 class="w-100 mb-3">
          Leading Text:
        </h4>
        <div class="mb-4">
          <InputField
            label="URL Input with Leading Text"
            placeholder="example.com"
            :add-on-text="true"
            :trailing-text="false"
            add-on-leading-text="https://"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Custom Leading Text"
            placeholder="username"
            :add-on-text="true"
            :trailing-text="false"
            add-on-leading-text="@"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <h4 class="w-100 mb-3 mt-4">
          Trailing Text:
        </h4>
        <div class="mb-4">
          <InputField
            label="Input with Trailing Text"
            placeholder="Enter amount"
            :add-on-text="true"
            :trailing-text="true"
            trailing-text="EUR"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="File Size Input"
            placeholder="Enter size"
            :add-on-text="true"
            :trailing-text="true"
            trailing-text="MB"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
      </div>
    </Variant>

    <!-- Date and Time Pickers -->
    <Variant title="Date and Time Pickers" icon="carbon:calendar">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <InputField
            label="Date Picker"
            placeholder="Select date"
            input-type="date"
            :add-on-text="false"
            :date-picker="true"
            :time-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Time Picker"
            placeholder="Select time"
            input-type="time"
            :add-on-text="false"
            :date-picker="false"
            :time-picker="true"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Date with Info Icon"
            placeholder="Select date"
            input-type="date"
            tooltip-text="Choose your preferred date"
            :add-on-text="false"
            :date-picker="true"
            :time-picker="false"
            :info-icon="true"
          />
        </div>
      </div>
    </Variant>

    <!-- Dropdown Variations -->
    <Variant title="Dropdown Variations" icon="carbon:dropdown">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <InputField
            label="Dropdown Field"
            placeholder="Select option"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
            :dropdown-dpi-v3="true"
            :drop-down-expanded="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Dropdown with Autocomplete"
            placeholder="Type to search"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
            :dropdown-dpi-v3="true"
            :autocomplete="true"
            :drop-down-expanded="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Expanded Dropdown"
            placeholder="Select option"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
            :dropdown-dpi-v3="true"
            :drop-down-expanded="true"
          />
        </div>
      </div>
    </Variant>

    <!-- Disabled States -->
    <Variant title="Disabled States" icon="carbon:locked">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <InputField
            label="Disabled Standard Input"
            placeholder="Cannot edit this"
            :is-disabled="true"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Disabled with Leading Text"
            placeholder="Cannot edit this"
            :is-disabled="true"
            :add-on-text="true"
            :trailing-text="false"
            add-on-leading-text="https://"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Disabled with Trailing Text"
            placeholder="Cannot edit this"
            :is-disabled="true"
            :add-on-text="true"
            :trailing-text="true"
            trailing-text="EUR"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Disabled Date Picker"
            placeholder="Cannot select date"
            input-type="date"
            :is-disabled="true"
            :add-on-text="false"
            :date-picker="true"
            :info-icon="false"
          />
        </div>
      </div>
    </Variant>

    <!-- Error States -->
    <Variant title="Error States" icon="carbon:warning">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <InputField
            label="Standard Input with Error"
            placeholder="Enter valid text"
            error-message="This field is required"
            :show-error="true"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Leading Text with Error"
            placeholder="example.com"
            error-message="Invalid URL format"
            :show-error="true"
            :add-on-text="true"
            :trailing-text="false"
            add-on-leading-text="https://"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Trailing Text with Error"
            placeholder="Enter amount"
            error-message="Amount must be positive"
            :show-error="true"
            :add-on-text="true"
            :trailing-text="true"
            trailing-text="EUR"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Date Picker with Error"
            placeholder="Select date"
            input-type="date"
            error-message="Date cannot be in the past"
            :show-error="true"
            :add-on-text="false"
            :date-picker="true"
            :info-icon="false"
          />
        </div>
      </div>
    </Variant>

    <!-- Input Types -->
    <Variant title="Input Types" icon="carbon:types">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <InputField
            label="Text Input"
            placeholder="Enter text"
            input-type="text"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Email Input"
            placeholder="Enter email"
            input-type="email"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Password Input"
            placeholder="Enter password"
            input-type="password"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Number Input"
            placeholder="Enter number"
            input-type="number"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="URL Input"
            placeholder="Enter URL"
            input-type="url"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
      </div>
    </Variant>

    <!-- Advanced Features -->
    <Variant title="Advanced Features" icon="carbon:settings">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <InputField
            label="Eraseable Input"
            placeholder="Text with delete button"
            :default-input="false"
            :eraseable="true"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="All Features Combined"
            placeholder="Enter text"
            tooltip-text="This input has all features enabled"
            supporting-hint-message="This is a hint message"
            :default-input="false"
            :eraseable="true"
            :add-on-text="false"
            :date-picker="true"
            :info-icon="true"
            :pre-icon="true"
            :show-end-icon="true"
          />
        </div>
        <div class="mb-4">
          <InputField
            label="Custom Hint Message"
            placeholder="Enter data"
            supporting-hint-message="Please enter valid data in the correct format"
            :add-on-text="false"
            :date-picker="false"
            :info-icon="false"
            :initial-hint-text="true"
          />
        </div>
      </div>
    </Variant>
  </Story>
</template>
