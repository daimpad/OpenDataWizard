<script setup>
import { ref, onMounted, watchEffect, computed } from 'vue'
import { useRoute } from 'vue-router';
import { isNil } from 'lodash';
import axios from 'axios';
import { useStore } from 'vuex';
import { getCurrentInstance } from "vue";
import { useI18n } from 'vue-i18n';
import { useDpiContext } from '../composables';
let env = getCurrentInstance().appContext.app.config.globalProperties.$env;

const store = useStore();
const dpiContext = useDpiContext();
const isDuplicate = ref(localStorage.getItem('dpi_duplicate') || false);
const isEditMode = computed(() => !!dpiContext.value.edit?.enabled)

const { t, locale } = useI18n({ useScope: 'global' });

const validationMessages = ref({
  idformatvalid: "",
  idunique: "",
  required: ""

});

onMounted(() => {
  // This is kind of buggy, its taking the strings from the wrong json (de and en is switched)
  validationMessages.value.idformatvalid = t('message.dataupload.datasets.datasetID.invalidFormat');
  validationMessages.value.idunique = t('message.dataupload.datasets.datasetID.duplicate');
  validationMessages.value.required = t('message.dataupload.datasets.datasetID.required');
});

const draftIDs = store.getters['auth/getUserDraftIds'];
const hubUrl = env.api.hubUrl;
function idunique(node) {
  const id = node?.value;

  return new Promise(async (resolve) => {
    // resolve(true) -> datasetId is unique
    // resolve(false) -> datasetId is not unique

    if (isNil(id) || id === '' || id === undefined) resolve(true)

    const existIdInUserDrafts = draftIDs?.includes(id)
    if (existIdInUserDrafts) resolve(false)

    const request = `${hubUrl}datasets/${id}?useNormalizedId=true`;
    try {
      const res = await axios.head(request);
      // if 2xx -> datsetId is not unique -> validation error
      const isNotUnique = !(res.status >= 200 && res.status < 300);
      if (isNotUnique) resolve(false)
    } catch (error) {
      // if 404 -> datasetId is unique
      resolve(true);
    }

    resolve(false)
  });
}

function idformatvalid(node) {
  return /^[a-z0-9-]*$/.test(node.value);
}

</script>

<template>
  <div class="formkitProperty DSid">
    <h4>{{ $t(`message.dataupload.datasets.datasetID.label`) }}</h4>
    <FormKit v-if="!isEditMode || isDuplicate" type="text" name="datasetID" id="datasetID"
      :placeholder="$t(`message.dataupload.datasets.datasetID.label`)"
      :info="$t(`message.dataupload.datasets.datasetID.info`)" :help="$t(`message.dataupload.datasets.datasetID.help`)"
      :validation-rules="{ idformatvalid, idunique }" validation="required|idformatvalid|(900)idunique"
      validation-visibility="live" :validation-messages="validationMessages" outer-class="formkitCmpWrap p-3">
    </FormKit>
    <FormKit v-else type="text" name="datasetID" id="datasetID" :disabled="true"
      :info="$t(`message.dataupload.datasets.datasetID.info`)" :help="$t(`message.dataupload.datasets.datasetID.help`)">
    </FormKit>
  </div>
</template>

<script>
export default {
  props: ['context']
}
</script>

<style></style>
