<script setup lang="ts">
import Dropdown from '../data-provider-interface/HappyFlowComponents/ui/Dropdown.vue'

const sampleData = [
  { '@value': 'Option 1', selected: false },
  { '@value': 'Option 2', selected: false },
  { '@value': 'Option 3', selected: false },
  { '@value': 'Option 4', selected: false },
  { '@value': 'Option 5', selected: false }
]

const multiData = [
  {
    headers: 'category1',
    list: [
      { alt_label: { de: 'Category 1 Item 1' }, selected: false },
      { alt_label: { de: 'Category 1 Item 2' }, selected: false },
      { alt_label: { de: 'Category 1 Item 3' }, selected: false }
    ]
  },
  {
    headers: 'category2',
    list: [
      { alt_label: { de: 'Category 2 Item 1' }, selected: false },
      { alt_label: { de: 'Category 2 Item 2' }, selected: false }
    ]
  },
  {
    headers: 'category3',
    list: [
      { alt_label: { de: 'Category 3 Item 1' }, selected: false },
      { alt_label: { de: 'Category 3 Item 2' }, selected: false },
      { alt_label: { de: 'Category 3 Item 3' }, selected: false },
      { alt_label: { de: 'Category 3 Item 4' }, selected: false }
    ]
  }
]

const longData = [
  { '@value': 'Very Long Option Name That Might Wrap', selected: false },
  { '@value': 'Another Extremely Long Option Name', selected: false },
  { '@value': 'Short', selected: false },
  { '@value': 'Medium Length Option', selected: false },
  { '@value': 'Yet Another Very Long Option Name That Tests Layout', selected: false }
]
</script>

<template>
  <Story title="Base Components/Dropdown">
    <!-- Text Button Dropdowns -->
    <Variant title="Text Button Dropdowns" icon="carbon:chevron-down">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Single Selection:
          </h4>
          <Dropdown
            button-text="Select Option"
            type="textButtonSmall"
            :text="{
              placeholder: 'Choose an option',
              property: 'Label',
              support: 'This is a supporting message'
            }"
            :data="sampleData"
          />
        </div>
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Multi Selection:
          </h4>
          <Dropdown
            button-text="Select Multiple"
            type="textButtonSmall"
            :text="{
              placeholder: 'Choose multiple options',
              property: 'Label',
              support: 'This is a supporting message'
            }"
            :data="multiData"
            :multi="true"
          />
        </div>
      </div>
    </Variant>

    <!-- More Button Dropdowns -->
    <Variant title="More Button Dropdowns" icon="carbon:overflow-menu-horizontal">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            More Button Style:
          </h4>
          <Dropdown
            button-text="More Actions"
            type="moreButton"
            :text="{
              placeholder: 'Select action',
              property: 'Action',
              support: 'Choose an action from the menu'
            }"
            :data="[
              { '@value': 'Edit', selected: false },
              { '@value': 'Delete', selected: false },
              { '@value': 'Share', selected: false },
              { '@value': 'Export', selected: false }
            ]"
          />
        </div>
      </div>
    </Variant>

    <!-- Input Field Dropdowns -->
    <Variant title="Input Field Dropdowns" icon="carbon:text-input">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Medium Width:
          </h4>
          <Dropdown
            button-text="Input Dropdown"
            type="inputField"
            dropdown-width="medium"
            :input-field-props="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: false,
              label: 'Select Category',
              dropdown_dpiV3: true,
              placeholder: 'Choose a category',
              inputFieldSize: 'medium'
            }"
            :data="sampleData"
          />
        </div>
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Large Width:
          </h4>
          <Dropdown
            button-text="Large Input Dropdown"
            type="inputField"
            dropdown-width="large"
            :input-field-props="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: false,
              label: 'HVD Category',
              dropdown_dpiV3: true,
              placeholder: 'Select HVD Category',
              inputFieldSize: 'large'
            }"
            :data="longData"
          />
        </div>
      </div>
    </Variant>

    <!-- Multi-Selection Dropdowns -->
    <Variant title="Multi-Selection Dropdowns" icon="carbon:checkbox-checked">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Categorized Multi-Selection:
          </h4>
          <Dropdown
            button-text="Multi Category Dropdown"
            type="inputField"
            dropdown-width="large"
            :input-field-props="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: false,
              label: 'Multiple Categories',
              dropdown_dpiV3: true,
              placeholder: 'Select multiple categories',
              inputFieldSize: 'large'
            }"
            :data="multiData"
            :multi="true"
          />
        </div>
      </div>
    </Variant>

    <!-- Disabled States -->
    <Variant title="Disabled States" icon="carbon:locked">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Disabled Text Button:
          </h4>
          <Dropdown
            button-text="Disabled Dropdown"
            type="textButtonSmall"
            :is-disabled="true"
            :text="{
              placeholder: 'Cannot select',
              property: 'Label',
              support: 'This dropdown is disabled'
            }"
            :data="sampleData"
          />
        </div>
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Disabled Input Field:
          </h4>
          <Dropdown
            button-text="Disabled Input Dropdown"
            type="inputField"
            dropdown-width="large"
            :is-disabled="true"
            :input-field-props="{
              isDisabled: true,
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: false,
              label: 'Disabled Category',
              dropdown_dpiV3: true,
              placeholder: 'Cannot select category',
              inputFieldSize: 'large'
            }"
            :data="sampleData"
          />
        </div>
      </div>
    </Variant>

    <!-- Loading States -->
    <Variant title="Loading States" icon="carbon:circle-dash">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Loading Dropdown:
          </h4>
          <Dropdown
            button-text="Loading Dropdown"
            type="inputField"
            dropdown-width="medium"
            :loading="true"
            :input-field-props="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: false,
              label: 'Loading Data',
              dropdown_dpiV3: true,
              placeholder: 'Loading options...',
              inputFieldSize: 'medium'
            }"
            :data="[]"
          />
        </div>
      </div>
    </Variant>

    <!-- Empty States -->
    <Variant title="Empty States" icon="carbon:no-image">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            No Data Available:
          </h4>
          <Dropdown
            button-text="Empty Dropdown"
            type="inputField"
            dropdown-width="medium"
            :input-field-props="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: false,
              label: 'No Options',
              dropdown_dpiV3: true,
              placeholder: 'No options available',
              inputFieldSize: 'medium'
            }"
            :data="[]"
          />
        </div>
      </div>
    </Variant>

    <!-- Autocomplete Variations -->
    <Variant title="Autocomplete Variations" icon="carbon:search">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Autocomplete Enabled:
          </h4>
          <Dropdown
            button-text="Autocomplete Dropdown"
            type="inputField"
            dropdown-width="large"
            :autocomplete="true"
            :input-field-props="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: true,
              label: 'Search Options',
              dropdown_dpiV3: true,
              placeholder: 'Type to search...',
              inputFieldSize: 'large'
            }"
            :data="[
              { '@value': 'Technology', selected: false },
              { '@value': 'Science', selected: false },
              { '@value': 'Environment', selected: false },
              { '@value': 'Health', selected: false },
              { '@value': 'Education', selected: false },
              { '@value': 'Transportation', selected: false },
              { '@value': 'Finance', selected: false },
              { '@value': 'Agriculture', selected: false }
            ]"
          />
        </div>
      </div>
    </Variant>

    <!-- Alignment Variations -->
    <Variant title="Alignment Variations" icon="carbon:align-horizontal-right">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <h4 class="w-100 mb-3">
            Right Aligned Dropdown:
          </h4>
          <div class="d-flex justify-content-end">
            <Dropdown
              button-text="Right Aligned"
              type="textButtonSmall"
              :alignment="true"
              :text="{
                placeholder: 'Right aligned options',
                property: 'Label',
                support: 'This dropdown aligns to the right'
              }"
              :data="sampleData"
            />
          </div>
        </div>
      </div>
    </Variant>

  </Story>
</template>
