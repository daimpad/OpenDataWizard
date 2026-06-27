<template>

  <div class="position-relative w-100 ">
    <FormKit name="mode" validation="required" type="text" class="selectInputField formkit-inner " readonly="readonly"
      @click="triggerDropdown()" :placeholder="t('message.dataupload.info.preferredInput')" v-model="inputChoice" :validation-messages="{
        required: t('message.dataupload.info.preferredInput'),
      }" />
    <ul ref="fLoad" v-if="drop.active" class="selectListUpload fileuploadList">
      <li @click="uploadFileSwitch = true; toggleUploadUrl()"
        class="p-2 border-b border-gray-200 data-[selected=true]:bg-blue-100 choosableItemsAC">{{$t('message.dataupload.datasets.conditional.fileupload')}}</li>
      <li @click="uploadURL = true; toggleUploadFileSwitch()"
        class="p-2 border-b border-gray-200 data-[selected=true]:bg-blue-100 choosableItemsAC">{{$t('message.dataupload.datasets.conditional.URL')}}</li>
    </ul>
  </div>
  <div class="w-100 position-relative" v-if="uploadURL && !uploadFileSwitch">

    <FormKit id="aUrlLink" v-model="URLValue" class="selectInputField formkit-inner" type="url" name="@id" :placeholder="context.attrs.placeholder"
      @input="saveUrl" validation="required|url" validation-visibility="live" :validation-messages="{
        required: t('message.dataupload.datasets.conditional.URL'),
        url: t('message.dataupload.info.urlFormat')
      }" />

  </div>
  <div v-if="uploadFileSwitch" ref="fileupload" class="p-3 w-100"
    :class="`formkit-input-element formkit-input-element--${context.type}`" :data-type="context.type" v-bind="$attrs">
    <input type="text" v-model="context.model" @blur="context.blurHandler" hidden />
    <div class="file-div position-relative">
      <input class="mt-3" type="file" id="aUrlFL" name="fileUpload" @change="validateFile($event)"
        :accept="validExtensions">
      <div class="upload-feedback position-absolute d-flex" style="right: 0">
        <div v-if="isLoading" class="lds-ring">
        </div>
        <div v-if="success"><i class="material-icons d-flex check-icon">check_circle</i></div>
        <div v-if="fail"><i class="material-icons d-flex close-icon">error</i></div>
      </div>
    </div>
    <p class="dURLText my-3" v-if="success">{{ $t('message.metadata.downloadUrl') }}: <a :href="context.model">{{
      context.model }}</a></p>
    <div v-if="validExtensions && validExtensions.length" class="allowedTypesWrapper">
      <p class="errorSub my-3 d-flex " v-if="!success">Allowed types: </p>
      <div class="d-flex flex-wrap w-100">
        <span v-for="types in validExtensions" :key="types" class="mr-1 mb-1 allowedFTypes ">
          {{ types }}
        </span>
      </div>

    </div>


  </div>

</template>

<script>
/* eslint-disable consistent-return, no-unused-vars */
import { mapGetters, mapActions } from 'vuex';
import axios from 'axios';
import helper from '../utils/general-helper'
import { getNode } from '@formkit/core'

import { reactive, ref, onMounted, computed } from 'vue';
import { onClickOutside } from '@vueuse/core'
import { useRuntimeEnv } from "../../composables/useRuntimeEnv";
import { FormKit } from '@formkit/vue';
import { useI18n } from 'vue-i18n';

export default {
  props: {
    context: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      inputChoice: '',
      error: '',
      URLValue: '',
      uploadURL: false,
      uploadFileSwitch: false,
      checkifSet: false,
      isLoading: false,
      success: false,
      fail: false,
    
      validExtensions: this.$env.content.dataProviderInterface.uploadFileTypes?.split(',') || []
    };
  },
  computed: {

    ...mapGetters('auth', [
      'getUserData',
      'getIsEditMode'
    ]),
    ...mapGetters('dpiStore', [
      'getData',
    ]),
    getCatalogue() {
      return getNode('dcat:catalog').value;
    }
  },
  methods: {
    ...mapActions('dpiStore', [
      'saveLocalstorageValues',
    ]),
    toggleUploadUrl() {
      this.inputChoice = this.t('message.dataupload.datasets.conditional.fileupload')
      if (this.uploadURL) { this.uploadURL = !this.uploadURL }
    },
    toggleUploadFileSwitch() {
      this.inputChoice = this.t('message.dataupload.datasets.conditional.URL')
      if (this.uploadFileSwitch) { this.uploadFileSwitch = !this.uploadFileSwitch }
    },
    validateFile(event) {
      const file = event.target.files[0];
      const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
      if (this.validExtensions && this.validExtensions.length) {
        if (!this.validExtensions.includes(fileExtension)) {
          console.log('Wrong filetype');
        } else {
          this.uploadOrReplaceFile({ file: event.target.files[0] })
        }
      } else {
        this.error = ""
        this.uploadOrReplaceFile({ file: event.target.files[0] })
      }
    },
    async saveUrl() {

      if (this.URLValue.includes('http://') || this.URLValue.includes('https://')) {
        await this.context.node.input({ '@id': this.URLValue, 'mode': this.inputChoice })
      }
      else await this.context.node.input({ '@id': 'https://' + this.URLValue, 'mode': this.inputChoice })

    },
    checkIfPresent() {
      // console.log(this.context.value['@id']);
      if (this.context.value['@id']) {
        // console.log(this.context.value['@id']);
        this.URLValue = this.context.value['@id']
        return true
      }
      else false
    },
    // finds the parent input group of a given element.
    findParentInputGroupOfElement(element) {
      // Start with the given element.
      let currentElement = element;

      // Traverse the DOM tree upwards.
      while (currentElement) {
        // If the current element is an input group, return it.
        if (currentElement.classList.contains('formkit-input-group-repeatable')) {
          return currentElement;
        }
        // If not, move to the parent element.
        currentElement = currentElement.parentElement;
      }

      // If no input group was found, return null.
      return null;
    },
    // finds the index of the distribution access URL based on the root of this component.
    findDistributionAccessUrlIndex() {
      // todo: find a more stable way to find the index of the distribution access URL.
      // this way uses the DOM tree, which is not stable.

      // Start at the root of this component.
      const rootElement = this.$refs.fileupload;

      // Find the parent input group of the root element.
      const parentInputGroup = this.findParentInputGroupOfElement(rootElement);
      if (!parentInputGroup) return null;

      // Get the parent element of all input groups.
      const parentOfAllInputGroups = parentInputGroup.parentElement;
      const allInputGroupsNodeList = parentOfAllInputGroups.querySelectorAll('.formkit-input-group-repeatable');
      const allInputGroupsArray = Array.from(allInputGroupsNodeList);

      // Find the index of the parent input group within the array of all input groups.
      const indexOfParentInputGroup = allInputGroupsArray.indexOf(parentInputGroup);

      return indexOfParentInputGroup;
    },
    async uploadOrReplaceFile({ file }) {

      const replaceEnabled = this.$env?.content?.dataProviderInterface?.enableFileUploadReplace || false;
      const wantsToReplace = this.$route.query?.edit ?? false;

      if (replaceEnabled && wantsToReplace) {
        const distributionIndexToReplace = this.$route.query?.edit;
        const fileIndexToReplace = this.findDistributionAccessUrlIndex();

        const targetDistribution = this.getData('distributions')?.[distributionIndexToReplace];
        const targetFile = targetDistribution?.['dcat:accessURL']?.[fileIndexToReplace];
        const accessUrl = targetFile?.['@id'];
        if (accessUrl) {
          const fileUploadUrl = this.$env.api.fileUploadUrl;

          const fileId = helper.getFileIdByAccessUrl({ accessUrl, fileUploadUrl })

          return await this.uploadFile(file, {
            method: 'PUT',
            url: `${this.$env.api.fileUploadUrl}data/${fileId}?catalog=${this.getCatalogue}`,
          });
        }

      }

      return await this.uploadFile(file);
    },
    async uploadFile(file, options = {}) {

      this.isLoading = true;

      const form = new FormData();
      form.append('file', file);

      const catalog = this.getCatalogue;
      const token = this.getUserData.rtpToken;

      const resolvedOptions = {
        method: 'POST',
        url: `${this.$env.api.fileUploadUrl}data?catalog=${catalog}`,
        ...options,
      };

      const requestOptions = {
        method: resolvedOptions.method,
        url: resolvedOptions.url,
        headers: {
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${token}`,
        },
        data: form,
      };

      try {

        const result = await axios.request(requestOptions);
        const path = result.data.result.location.substring(result.data.result.location.indexOf('/') + 1);
        this.context.model = `${this.$env.api.fileUploadUrl}${path}`;
        this.isLoading = false;
        this.success = true;
        await this.context.node.input({ '@id': `${this.$env.api.fileUploadUrl}${path}` })
        // this.context.rootEmit('change');

      } catch (err) {

        this.isLoading = false;
        this.fail = true;
        console.error(err); // eslint-disable-line
      }
    },
  },
  mounted() {

    this.$nextTick(function () {

      if (this.context.value['@id']) {
        this.uploadURL = true
        this.URLValue = this.context.value['@id']
        this.inputChoice = "Provide an URL"
        return true
      }
      else false
    })
  },
  setup() {
    const { t } = useI18n();
    const env = useRuntimeEnv();
 


    var drop = reactive({
      active: false,
    })


    const fLoad = ref('fload');

    onClickOutside(fLoad, event => {
      drop.active = false
    })
    function triggerDropdown(e) {
      drop.active = !drop.active
    }

    return {
      drop,
      onClickOutside,
      triggerDropdown,
      t
    };
  }
};
</script>

<style lang="scss" scoped>
// @import '../../../styles/bootstrap_theme';
// @import '../../../styles/utils/css-animations';
.dURLText {}


.accessUrl {
  .formkit-outer {}
}

.file-div {
  display: flex;
  align-items: center;
}

.upload-feedback {
  padding: 10px;
}

/*** MATERIAL ICONS ***/
%modal-icon {
  font-size: 20px;
  cursor: default;
}

.check-icon {
  @extend %modal-icon;
  color: #28a745;
}

.close-icon {
  @extend %modal-icon;
  color: red;
}

.lds-ring {
  display: inline-block;
  position: relative;
  width: 30px;
  height: 30px;
}

.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  right: 0;
  width: 30px;
  height: 30px;
  border: 8px solid lightgray;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: lightgray transparent transparent transparent;
}

.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}

.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}

.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}

.error {
  color: red;
}

.errorSub {
  color: black;
}

@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.allowedFTypes {
  padding: 0.5rem;
  border-radius: 5px;
  border: 1px solid lightgrey;
}

.allowedTypesWrapper {
  max-width: 100%;
  display: flex;
  flex-wrap: wrap;
}

.fileuploadList {
  width: 96.7%;
}
</style>
