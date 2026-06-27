<script setup lang="ts">
import ButtonV3 from '../data-provider-interface/HappyFlowComponents/ui/ButtonV3.vue'

const sizes = ['small', 'medium', 'large'] as const
const variants = ['primary', 'secondary', 'tertiary'] as const
const icons = ['MagnifyingGlass', 'Check', 'CaretLeft', 'CaretRight', 'plus', 'trash'] as const
</script>

<template>
  <Story title="Base Components/ButtonV3">
    <!-- Basic Text Buttons -->
    <Variant title="Text Buttons - Primary" icon="carbon:button-flush">
      <div class="d-flex flex-row align-items-center">
        <ButtonV3
          v-for="size in sizes"
          :key="size"
          :button-text="`Button ${size.charAt(0).toUpperCase() + size.slice(1)}`"
          :size="size"
          variant="primary"
          class="mr-3"
        />
      </div>
    </Variant>

    <Variant title="Text Buttons - Secondary" icon="carbon:button-flush">
      <div class="d-flex flex-row align-items-center">
        <ButtonV3
          v-for="size in sizes"
          :key="size"
          :button-text="`Button ${size.charAt(0).toUpperCase() + size.slice(1)}`"
          :size="size"
          variant="secondary"
          class="mr-3"
        />
      </div>
    </Variant>

    <Variant title="Text Buttons - Tertiary" icon="carbon:button-flush">
      <div class="d-flex flex-row align-items-center">
        <ButtonV3
          v-for="size in sizes"
          :key="size"
          :button-text="`Button ${size.charAt(0).toUpperCase() + size.slice(1)}`"
          :size="size"
          variant="tertiary"
          class="mr-3"
        />
      </div>
    </Variant>

    <!-- Buttons with Leading Icons -->
    <Variant title="Leading Icon Buttons" icon="carbon:add">
      <div>
        <div v-for="variant in variants" :key="variant" class="mb-4">
          <div class="text-uppercase font-weight-bold mb-2">
            {{ variant }}
          </div>
          <div class="d-flex flex-row align-items-center">
            <ButtonV3
              v-for="size in sizes"
              :key="size"
              :button-text="`${variant.charAt(0).toUpperCase() + variant.slice(1)} ${size}`"
              :size="size"
              :variant="variant"
              icon-start="MagnifyingGlass"
              class="mr-3"
            />
          </div>
        </div>
      </div>
    </Variant>

    <!-- Buttons with Trailing Icons -->
    <Variant title="Trailing Icon Buttons" icon="carbon:arrow-right">
      <div>
        <div v-for="variant in variants" :key="variant" class="mb-4">
          <div class="text-uppercase font-weight-bold mb-2">
            {{ variant }}
          </div>
          <div class="d-flex flex-row align-items-center">
            <ButtonV3
              v-for="size in sizes"
              :key="size"
              :button-text="`${variant.charAt(0).toUpperCase() + variant.slice(1)} ${size}`"
              :size="size"
              :variant="variant"
              icon-end="CaretRight"
              class="mr-3"
            />
          </div>
        </div>
      </div>
    </Variant>

    <!-- Icon-Only Buttons -->
    <Variant title="Icon-Only Buttons" icon="carbon:circle-dash">
      <div>
        <div v-for="variant in variants" :key="variant" class="mb-4">
          <div class="text-uppercase font-weight-bold mb-2">
            {{ variant }}
          </div>
          <div class="d-flex flex-row align-items-center">
            <ButtonV3
              v-for="size in sizes"
              :key="size"
              :size="size"
              :variant="variant"
              icon-start="MagnifyingGlass"
              class="mr-3"
            />
          </div>
        </div>
      </div>
    </Variant>

    <!-- Disabled States -->
    <Variant title="Disabled Buttons" icon="carbon:locked">
      <div>
        <div v-for="variant in variants" :key="variant" class="mb-4">
          <div class="text-uppercase font-weight-bold mb-2">
            {{ variant }}
          </div>
          <div class="d-flex flex-row align-items-center">
            <ButtonV3
              v-for="size in sizes"
              :key="size"
              :button-text="`Disabled ${size}`"
              :size="size"
              :variant="variant"
              :disabled="true"
              class="mr-3"
            />
          </div>
        </div>
      </div>
    </Variant>

    <!-- Different Icon Types -->
    <Variant title="Icon Variations" icon="carbon:apps">
      <div>
        <div class="font-weight-bold mb-3">
          Leading Icons
        </div>
        <div class="d-flex align-items-center flex-wrap mb-4">
          <ButtonV3
            v-for="icon in icons"
            :key="icon"
            :button-text="icon"
            size="medium"
            variant="primary"
            :icon-start="icon"
            class="mr-2 mb-2"
          />
        </div>

        <div class="font-weight-bold mb-3">
          Trailing Icons
        </div>
        <div class="d-flex align-items-center flex-wrap">
          <ButtonV3
            v-for="icon in icons.slice(0, 4)"
            :key="icon"
            :button-text="icon"
            size="medium"
            variant="secondary"
            :icon-end="icon"
            class="mr-2 mb-2"
          />
        </div>
      </div>
    </Variant>
  </Story>
</template>
