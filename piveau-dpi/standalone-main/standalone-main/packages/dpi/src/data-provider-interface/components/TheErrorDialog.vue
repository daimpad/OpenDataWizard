
<script setup lang="ts">
/**
 * Component for displaying DPI errors as a modal dialog
 */

import DpiModalBase from '../HappyFlowComponents/ui/DpiModalBase.vue';
import ButtonV3 from '../HappyFlowComponents/ui/ButtonV3.vue';
import { useErrorDialog } from '../composables/useErrorDialog';
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useRuntimeEnv } from '../../composables/useRuntimeEnv';

const router = useRouter()
const route = useRoute()
const { open, error, currentError, userMessage, openErrorDialog, closeErrorDialog } = useErrorDialog()

const env = useRuntimeEnv()

// todo
const isDevelopment = true || import.meta.env.DEV

const continueAnyway = () => { open.value = false }
const exit = () => { router.push('/') }

const errorReport = computed(() => {
  return `${currentError?.value?.message}

----

id = ${route.query?.id}; fromDraft = ${route.query?.fromDraft}; edit = ${route.query?.edit}

----

${error?.value?.stack}

----

${JSON.stringify(env.api, null, 2)}

----

${JSON.stringify(env.authentication.keycloak, null, 2)}

`

})
</script>

<template>
  <DpiModalBase :persistent="!isDevelopment" v-model="open" onClose="() => {}">
    <template #header>
      <h2 class="dpiV3_modalTitle"><strong>Fehler beim Laden des Datensatzes</strong></h2>
    </template>
    <div v-if="error" class="dpiV3_modalErrorContent">
      <div class="dpiV3_modalErrorMain">
        <p v-if="userMessage" class="error-message">{{ userMessage }}</p>
        <p class="error-message">Ein Fehler ist aufgetreten. Die folgenden Details können den Betreibenden bei der
          Fehleranalyse helfen.</p>
        <div class="development-notice">
          <textarea readonly class="error-textarea" :value="errorReport"></textarea>
        </div>
      </div>
      <div class="dpiV3_modalErrorFooter" v-if="false && isDevelopment">
        <p class="development-notice">
          <em>Note: The Continue (Forced) button is only visible in development mode for debugging purposes.</em>
        </p>
      </div>
    </div>
    <template #footer>
      <div class="dpiV3_modalActions">
        <ButtonV3 v-if="isDevelopment" @click="continueAnyway" buttonText="Trotzdem Fortfahren" variant="secondary" />
        <ButtonV3 @click="exit" buttonText="Schließen" variant="primary" />
      </div>
    </template>
  </DpiModalBase>
</template>