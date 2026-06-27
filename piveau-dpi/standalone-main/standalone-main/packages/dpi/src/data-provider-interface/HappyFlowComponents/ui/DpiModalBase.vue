<template>
  <TransitionRoot
    :show="isOpenProxy"
    as="template"
  >
    <Dialog
      @close="isOpenProxy = false"
      style="position: relative;z-index: 10000;"
    >
      <TransitionChild
        enter="dpiV3__backdrop-transition--enter"
        leave="dpiV3__backdrop-transition--leave"
        enter-from="dpiV3__backdrop-transition--enter-from"
        enter-to="dpiV3__backdrop-transition--enter-to"
        leave-from="dpiV3__backdrop-transition--leave-from"
        leave-to="dpiV3__backdrop-transition--leave-to"
      >
        <div class="dpiV3_backdrop"></div>
      </TransitionChild>
      <div class="dpiV3_RapModalContainer">
        <TransitionChild
          enter="dpiV3__modal-transition--enter"
          leave="dpiV3__modal-transition--leave"
          enter-from="dpiV3__modal-transition--enter-from"
          enter-to="dpiV3__modal-transition--enter-to"
          leave-from="dpiV3__modal-transition--leave-from"
          leave-to="dpiV3__modal-transition--leave-to"
        >
          <DialogPanel class="dpiV3_RapModalOuter">
            <div class="dpiV3_RapModalInner">
              <div class="dpiV3_modalHead">
                <slot name="header">
                  <!-- Default header content -->
                </slot>
                <div v-if="!properties.persistent" class="dpiV3_closeButtonContainer">
                  <CrossOutButton @click="isOpenProxy = false" class="dpiV3_closeButton" type="default" />
                </div>
              </div>
              <div class="dpiV3_modalBody">
                <slot>
                  <!-- Modal content goes here -->
                </slot>
              </div>
            </div>
            <div class="dpiV3_interactionWrap">
              <slot name="footer">
                <!-- Default footer content -->
              </slot>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue';
import CrossOutButton from './CrossOutButton.vue';
import { computed } from 'vue';

const properties = defineProps<{
    persistent?: boolean
}>();

const modelValue = defineModel<boolean>();
const isOpenProxy = computed({
    get() {
        return modelValue.value;
    },
    set(value: boolean) {
        // We block any changes to the isOpen value if the modal is persistent.
        // It's an ugly workaround and it prevents us from animating upon closing the modal,
        // but HeadlessUI as of now doesn't support persistent modals.
        // Thus the only way to hide this modal is from outside this component (with a v-if).
        if (properties.persistent && !value) {
            return;
        }

        modelValue.value = value;
    },
});

</script>

<style scoped lang="scss">
.dpiV3 {
  &__backdrop-transition {
    &--enter {
      transition-duration: 150ms;
      transition-timing-function: ease-out;
    }
    &--enter-from {
      opacity: 0;
    }
    &--enter-to {
      opacity: 1;
    }
    &--leave {
      transition-duration: 100ms;
      transition-timing-function: ease-in;
    }
    &--leave-from {
      opacity: 1;
    }
    &--leave-to {
      opacity: 0;
    }
  }

  &__modal-transition {
    &--enter {
      transition-duration: 200ms;
      transition-timing-function: ease-out;
    }
    &--enter-from {
      opacity: 0;
      transform: scale(0.95);
    }
    &--enter-to {
      opacity: 1;
      transform: scale(1);
    }
    &--leave {
      transition-duration: 150ms;
      transition-timing-function: ease-in;
    }
    &--leave-from {
      opacity: 1;
      transform: scale(1);
    }
    &--leave-to {
      opacity: 0;
      transform: scale(0.95);
    }
  }
}

/* Base modal styles - specific styling should be done in the parent component */
.dpiV3_modalActions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.dpiV3_button {
  padding: 8px 16px;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border: none;
  outline: none;
  
  &--primary {
    background-color: #2196F3;
    color: white;
    
    &:hover {
      background-color: #1976D2;
    }
  }
  
  &--secondary {
    background-color: #E0E0E0;
    color: #333;
    
    &:hover {
      background-color: #BDBDBD;
    }
  }
}

.dpiV3_modalBody {
  width: 100%;
  position: relative;
  z-index: 1;
}

.dpiV3_interactionWrap {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  align-self: flex-end;
  width: 100%;
}

.dpiV3_RapModalInner {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  position: relative;
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
}

.dpiV3_closeButtonContainer {
  position: absolute;
  right: 0;
  // top: 0;
  z-index: 1000;
}

.dpiV3_RapModalOuter {
  display: flex;
  width: 624px;
  padding: 0 var(--Spacing-5, 32px) var(--Spacing-5, 32px) var(--Spacing-5, 32px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-8, 64px);
  position: relative;
  border-radius: var(--Modal-Radius, 32px);
  background: var(--neutral-l0, #FFF);
  margin-top: 84px;
}

.dpiV3_RapModalContainer::-webkit-scrollbar {
  display: none;
}

.dpiV3_backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(11, 26, 37, 0.7);
}

.dpiV3_RapModalContainer {
  position: fixed;
  inset: 0;
  overflow-y: auto;
  width: 100vw;
  left: 0;
  height: 100vh;
  top: 0;
  display: flex;
  align-items: start;
  justify-content: center;
}
</style>
