<script setup lang="ts">
import Chip from '../data-provider-interface/HappyFlowComponents/ui/Chip.vue'
</script>

<template>
  <Story title="Base Components/Chip">
    <!-- Basic Chip Types -->
    <Variant title="Basic" icon="carbon:tag">
      <div class="d-flex flex-row align-items-center flex-wrap gap-3">
        <Chip
          text="Default Chip"
          :data="{ '@value': 'Default Chip', 'URI': 'https://example.com/default' }"
          :setup="{ '@type': 'default', '@inTable': false }"
        />
        <Chip
          text="Selectable Chip"
          :data="{ '@value': 'Selectable Chip', 'URI': 'https://example.com/select' }"
          :setup="{ '@type': 'select', '@inTable': false }"
        />
        <Chip
          text="Selected Chip"
          :data="{ '@value': 'Selected Chip', 'URI': 'https://example.com/selected' }"
          :setup="{ '@type': 'selected', '@inTable': false }"
        />
        <Chip
          text="Disabled Chip"
          :data="{ '@value': 'Disabled Chip', 'URI': 'https://example.com/disabled' }"
          :setup="{ '@type': 'disabled', '@inTable': false }"
        />
        <Chip
          text="Static Chip"
          :data="{ '@value': 'Static Chip', 'URI': 'https://example.com/static' }"
          :setup="{ '@type': 'static', '@inTable': false }"
        />
      </div>
    </Variant>

    <!-- Search Icon Chips -->
    <Variant title="Search Icon Chips" icon="carbon:search">
      <div class="d-flex flex-row align-items-center flex-wrap gap-3">
        <h4 class="w-100 mb-3">
          Prefix Search Icons:
        </h4>
        <Chip
          v-for="type in ['default', 'select', 'static']"
          :key="`prefix-${type}`"
          :text="`${type.charAt(0).toUpperCase() + type.slice(1)} Search`"
          :data="{ '@value': `${type} search`, 'URI': 'https://example.com/search' }"
          :setup="{ '@type': type, '@icon': 'prefix', '@search': true, '@inTable': false }"
        />
        <h4 class="w-100 mb-3 mt-4">
          Suffix Search Icons:
        </h4>
        <Chip
          v-for="type in ['default', 'select', 'static']"
          :key="`suffix-${type}`"
          :text="`${type.charAt(0).toUpperCase() + type.slice(1)} Search`"
          :data="{ '@value': `${type} search`, 'URI': 'https://example.com/search' }"
          :setup="{ '@type': type, '@icon': 'suffix', '@search': true, '@inTable': false }"
        />
      </div>
    </Variant>

    <!-- Close Icon Chips -->
    <Variant title="Close Icon Chips" icon="carbon:close">
      <div class="d-flex flex-row align-items-center flex-wrap gap-3">
        <h4 class="w-100 mb-3">
          Prefix Close Icons:
        </h4>
        <Chip
          v-for="type in ['select', 'selected']"
          :key="`prefix-close-${type}`"
          :text="`${type.charAt(0).toUpperCase() + type.slice(1)} Close`"
          :data="{ '@value': `${type} close`, 'URI': 'https://example.com/close' }"
          :setup="{ '@type': type, '@icon': 'prefix', '@search': false, '@inTable': false, '@selected': type === 'selected' }"
        />
        <h4 class="w-100 mb-3 mt-4">
          Suffix Close Icons:
        </h4>
        <Chip
          v-for="type in ['select', 'selected']"
          :key="`suffix-close-${type}`"
          :text="`${type.charAt(0).toUpperCase() + type.slice(1)} Close`"
          :data="{ '@value': `${type} close`, 'URI': 'https://example.com/close' }"
          :setup="{ '@type': type, '@icon': 'suffix', '@search': false, '@inTable': false, '@selected': type === 'selected' }"
        />
      </div>
    </Variant>

    <!-- Disabled States -->
    <Variant title="Disabled States" icon="carbon:locked">
      <div class="d-flex flex-row align-items-center flex-wrap gap-3">
        <Chip
          text="Disabled Default"
          :data="{ '@value': 'Disabled Default', 'URI': 'https://example.com/disabled' }"
          :setup="{ '@type': 'disabled', '@inTable': false }"
        />
        <Chip
          text="Disabled Selected"
          :data="{ '@value': 'Disabled Selected', 'URI': 'https://example.com/disabled' }"
          :setup="{ '@type': 'disabled', '@inTable': false, '@selected': true }"
        />
        <Chip
          text="Disabled with Icon"
          :data="{ '@value': 'Disabled with Icon', 'URI': 'https://example.com/disabled' }"
          :setup="{ '@type': 'disabled', '@icon': 'suffix', '@search': true, '@inTable': false }"
        />
      </div>
    </Variant>

    <!-- Table Context Chips -->
    <Variant title="Table Context Chips" icon="carbon:table">
      <div class="d-flex flex-row align-items-center flex-wrap gap-3">
        <h4 class="w-100 mb-3">
          In Table Context:
        </h4>
        <Chip
          text="Table Static"
          :data="{ '@value': 'Table Static', 'URI': 'https://example.com/table' }"
          :setup="{ '@type': 'static', '@inTable': true }"
        />
        <Chip
          text="Table with Search"
          :data="{ '@value': 'Table with Search', 'URI': 'https://example.com/table' }"
          :setup="{ '@type': 'static', '@inTable': true, '@icon': 'prefix', '@search': true }"
        />
        <Chip
          text="Table Selectable"
          :data="{ '@value': 'Table Selectable', 'URI': 'https://example.com/table' }"
          :setup="{ '@type': 'select', '@inTable': true, '@icon': 'suffix' }"
        />
      </div>
    </Variant>

    <!-- Mixed Variations -->
    <Variant title="Mixed Variations" icon="carbon:mixed-reference">
      <div class="d-flex flex-row align-items-center flex-wrap gap-3">
        <Chip
          text="Technology"
          :data="{ '@value': 'Technology', 'URI': 'https://example.com/tech' }"
          :setup="{ '@type': 'select', '@icon': 'prefix', '@search': true, '@inTable': false }"
        />
        <Chip
          text="Science"
          :data="{ '@value': 'Science', 'URI': 'https://example.com/science' }"
          :setup="{ '@type': 'selected', '@icon': 'suffix', '@search': false, '@inTable': false }"
        />
        <Chip
          text="Environment"
          :data="{ '@value': 'Environment', 'URI': 'https://example.com/env' }"
          :setup="{ '@type': 'static', '@icon': 'prefix', '@search': true, '@inTable': false }"
        />
        <Chip
          text="Health"
          :data="{ '@value': 'Health', 'URI': 'https://example.com/health' }"
          :setup="{ '@type': 'select', '@inTable': false }"
        />
        <Chip
          text="Education"
          :data="{ '@value': 'Education', 'URI': 'https://example.com/edu' }"
          :setup="{ '@type': 'disabled', '@icon': 'suffix', '@search': false, '@inTable': false }"
        />
      </div>
    </Variant>

    <!-- Special Context Variations -->
    <Variant title="Special Context" icon="carbon:settings">
      <div class="d-flex flex-row align-items-center flex-wrap gap-3">
        <h4 class="w-100 mb-3">
          Findability Context:
        </h4>
        <Chip
          text="Findability Chip"
          :data="{ '@value': 'Findability', 'URI': 'https://example.com/findability' }"
          :setup="{ '@type': 'select', '@findability': true, '@inTable': false }"
        />
        <Chip
          text="RaP Findability"
          :data="{ '@value': 'RaP Findability', 'URI': 'https://example.com/rap' }"
          :setup="{ '@type': 'select', '@rapfindability': true, '@inTable': false }"
        />
        <h4 class="w-100 mb-3 mt-4">
          Combined Features:
        </h4>
        <Chip
          text="Complex Chip"
          :data="{ '@value': 'Complex Chip', 'URI': 'https://example.com/complex' }"
          :setup="{ '@type': 'selected', '@icon': 'prefix', '@search': true, '@inTable': true, '@findability': true }"
        />
      </div>
    </Variant>
  </Story>
</template>
