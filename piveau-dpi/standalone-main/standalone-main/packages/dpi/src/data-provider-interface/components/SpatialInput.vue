<script setup>

import { ref, reactive, watch, computed, onBeforeMount, onMounted, nextTick, watchEffect } from 'vue';
import { useStore } from 'vuex';
import { getTranslationFor } from "../../utils/helpers";
import { onClickOutside, whenever } from '@vueuse/core'
import { getCurrentInstance } from "vue";
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useTed } from '../../composables/useTed';
import { useAutocomplete } from '../composables/aucotomplete';
import { useRequestUriName } from '../composables/useRequestUriName';
import { useDpiContext } from '../composables';


let instance = getCurrentInstance().appContext.app.config.globalProperties.$env
let route = useRoute();
const dpiContext = useDpiContext();

const props = defineProps({
    context: Object,
})

const { requestAutocompleteSuggestions } = useAutocomplete()

// let listOfVoc: [{ item: 'Country', active: false }, { item: 'Place', active: false }, { item: 'Continent', active: false }],
let listOfVoc = ref([])
const typeText = ref('')
let inputText = ref({})
let voc = ref({})
let matches = ref({})
let manURL = ref({})
const store = useStore();
const { t, locale } = useI18n({ inheritLocale: true, useScope: 'global' });
const ted = useTed();

const man = ref(false)
const vocSearch = ref(false)

const isEdit = computed(() => !!dpiContext.value.edit?.enabled)
const resourceNameWhenEditing = computed(() => !!isEdit.value && props.context.value.resource)
// e.g. extract "municipalityKey" (second to last) out of resourceName
const keyFromResourceName = computed(() => {
    if (resourceNameWhenEditing.value) {
        return resourceNameWhenEditing.value.split('/')[resourceNameWhenEditing.value.split('/').length - 2]
    }
    return ''
})
const spatialVocabName = computed(() => {
  if (!keyFromResourceName.value) {
    return ''
  }

  const maybePrefix = props.context.attrs.identifier === 'politicalGeocodingURI'
    ? 'political-geocoding-'
    // todo: extend this for other vocabularies
    : ''

  // transform key from camelCase to kebab-case
  const vocabKey = keyFromResourceName.value.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase()
  return `${maybePrefix}${vocabKey}`
})

const translatedResourceName = computed(() => {
  // transform key 'districtKey' to 'District Key'
  const vocabKey = keyFromResourceName.value.replace(/([a-z])([A-Z])/g, '$1 $2').toLowerCase()
  // make first letters of each word uppercase
  const vocabKeyUpper = vocabKey.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
  return t(`message.dataupload.datasets.conditional.${vocabKeyUpper}`)
})

const { execute, state, isLoading } = useRequestUriName({
  voc: spatialVocabName,
  res: computed(() => props.context.value.resource),
  // property: 'dcatde:politicalGeocodingURI',
  property: computed(() => props.context.attrs.identifier === 'politicalGeocodingURI' ? 'dcatde:politicalGeocodingURI' : 'dct:spatial'),  
  locale,
})

const restoredValueFromEditMode = computed(() => {
  if (isEdit.value && !isLoading.value && resourceNameWhenEditing.value && state.value) {
    return `${translatedResourceName.value}: ${state.value} (${resourceNameWhenEditing.value.split('/').pop()})`
  }

  return ''
})

const once = ref(false)
watchEffect(() => {
  if (once.value) return;

  if (!!resourceNameWhenEditing.value) {
    once.value = true
    execute()
  }
}, { immediate: true })

whenever(restoredValueFromEditMode, () => {
  const v = { name: restoredValueFromEditMode.value, resource: resourceNameWhenEditing.value }
  props.context.node.input(v)
})

// If true, then:
// - hides the select input for manual and vocabulary
// - pre-selects the vocabulary option
// When using formkit schema, enable this option by setting vocabularyOnly to true
// {
//     $formkit: 'spatialinput',
//     name: 'dcatde:politicalGeocodingURI',
//     identifier: 'politicalGeocodingURI',
//     vocabularyOnly: true,
// }
const vocabularyOnly = computed(() => !!props.context?.attrs?.vocabularyOnly)

if (props.context.attrs.identifier === 'politicalGeocodingURI') {
    listOfVoc.value.push(
        { item: 'Municipality Key', active: false, placeholder: ted('message.dataupload.datasets.conditional.Municipality Key', 'Municipality Key') },
        { item: 'Regional Key', active: false, placeholder: ted('message.dataupload.datasets.conditional.Regional Key', 'Regional Key') },
        { item: 'Municipal Association Key', active: false, placeholder: ted('message.dataupload.datasets.conditional.Municipal Association Key', 'Municipal Association Key') },
        { item: 'District Key', active: false, placeholder: ted('message.dataupload.datasets.conditional.District Key', 'District Key') },
        { item: 'Government District Key', active: false, placeholder: ted('message.dataupload.datasets.conditional.Government District Key', 'Government District Key') },
        { item: 'State Key', active: false, placeholder: ted('message.dataupload.datasets.conditional.State Key', 'State Key') },
    )

}
if (props.context.attrs.identifier === 'spatial') {
    listOfVoc.value.push(
        { item: 'Country', active: false, placeholder: ted('message.dataupload.datasets.conditional.Country', 'Country') },
        { item: 'Place', active: false, placeholder: ted('message.dataupload.datasets.conditional.Place', 'Place') },
        { item: 'Continent', active: false, placeholder: ted('message.dataupload.datasets.conditional.Continent', 'Continent') },
    )
}

watch(inputText, async () => {
    getAutocompleteSuggestions();
})
watch(voc, async (newValue, oldValue) => {
    if (newValue === oldValue) { return }
    voc.value = voc.value.toLowerCase();
})
watch(manURL, async () => {
    props.context.node.input({ 'name': manURL, 'resource': manURL })
})
onMounted(async () => {
    matches = [{ name: ted('message.dataupload.info.searchVocabulary', '--- Type in anything for a live search of the vocabulary ---').value, resource: 'invalid' }]

    await nextTick()
    // DOM loaded
    if (props.context.value.name === undefined || props.context.value.name === "") {
        showTable.activeValue = false
    } else showTable.activeValue = true
    // console.log(showTable.activeValue);

    if (vocabularyOnly.value) {
        activeInput('showTable'); vocSearch.value = true; if (man.value) { man.value = false }
    }

});

function closeAll() {
    listOfVoc.value.forEach(element => {
        element.active = false;
    });
}
function removeProperty(e) {
    //   props.context.value = {}
    showTable.activeValue = false
    props.context.node.input({})
}
function saveToLocal(el) {

    let pathToLocalStorage = JSON.parse(localStorage.getItem(`dpi_${route.params.property}`));
    let arr
    if (props.context.attrs.identifier === 'politicalGeocodingURI') {
        arr = pathToLocalStorage.Advised['dcatde:politicalGeocodingURI'];
    }
    else arr = pathToLocalStorage.Advised['dct:spatial'];

    arr.forEach((element, index) => {
        if (Object.keys(element).length === 0) {
            arr.splice(index, 1)
        }
    })
    arr.push(props.context.node._value)
    if (props.context.attrs.identifier === 'politicalGeocodingURI') {
        pathToLocalStorage.Advised['dcatde:politicalGeocodingURI'] = arr
    }
    else pathToLocalStorage.Advised['dct:spatial'] = arr

    localStorage.setItem(`dpi_${route.params.property}`, JSON.stringify(pathToLocalStorage))
}
const getAutocompleteSuggestions = async () => {

    let vocCache = voc.value?.toLowerCase()

    if (props.context.attrs.identifier === 'politicalGeocodingURI') {

        vocCache = 'political-geocoding-' + vocCache.toLowerCase().replaceAll(" ", '-')

        try {
            let text = inputText.value;
            await requestAutocompleteSuggestions({ voc: vocCache, text: text, base: instance.api.baseUrl }).then((response) => {
                const results = response.data.result.results.map((r) => ({
                    name: getTranslationFor(r.alt_label, locale.value, []) + " (" + r.id + ")",
                    resource: r.resource,
                }));
                matches = results;
            });
        } catch (error) {
        }
    }
    else {
        try {
            let text = inputText.value;
            await requestAutocompleteSuggestions({ voc: vocCache, text: text, base: instance.api.baseUrl }).then((response) => {
                const results = response.data.result.results.map((r) => ({
                    name: getTranslationFor(r.pref_label, locale.value, []) + " (" + r.id + ")",
                    resource: r.resource,
                }));
                matches = results;
            });
        } catch (error) {
        }
    }

}

var showTable = reactive({
    first: false,
    second: false,
    third: false,
    activeValue: false
})

const I1 = ref(null);
const I2 = ref(null);
const I3 = ref(null);

onClickOutside(I1, event => showTable.first = false)
onClickOutside(I2, event => showTable.second = false)
onClickOutside(I3, event => showTable.third = false)
function activeInput(e) {

    // console.log('in', showTable);
    if (e === "showTable") showTable.first = !showTable.first;
    if (e === "showVocTable") showTable.second = !showTable.second;
    if (e === "showVocEntries") {
        if (showTable.third === true) {
        }
        else showTable.third = !showTable.third;
    }
}
function manURLInput(e) {
    props.context.node.input({ 'name': e.target.value, 'resource': e.target.value })
}

function handleSpatielListClick(el) {
  props.context.node.input(el);
  inputText.value = el.name;
  activeInput('showVocEntries');
  showTable.third = false;
  saveToLocal(el)
}

function submitType() {
    activeInput('showVocTable');

}
// console.log(voc);
</script>

<template>
    <div class="d-flex flex-column w-100 spatialWrap">
        <div class="d-flex formkit-inner mx-3 mb-3" v-if="!props.context.attrs.multiple && showTable.activeValue">
            <!-- <div class="infoI">
                <div class="tooltipFormkit">{{ props.context.attrs.info }}</div>
            </div> -->
            <a class="autocompleteInputSingleValue ">{{ restoredValueFromEditMode }}</a>
            <div class="removeX" @click="removeProperty"></div>
        </div>
        <div v-else>
            <div class=" w-100 d-flex">
                <div v-if="!vocabularyOnly" class="d-flex position-relative m-3 w-100">
                    <label class="w-100"> {{ $t('message.dataupload.info.preferredInput') }}
                        <input id="I1" type="text" class="selectInputField formkit-inner" readonly="readonly"
                            :placeholder="$t('message.dataupload.info.preferredInput')"
                            @click="activeInput('showTable')" />
                    </label>

                    <ul ref="I1" v-show="showTable.first" class="spatialListUpload">
                        <li @click="activeInput('showTable'); man = true; if (vocSearch) { vocSearch = false; };"
                            class="p-2 border-b border-gray-200 choosableItemsAC">
                            {{ $t('message.dataupload.datasets.conditional.manually') }}
                        </li>
                        <li @click="activeInput('showTable'); vocSearch = true; if (man) { man = false }"
                            class="p-2 border-b border-gray-200 choosableItemsAC">
                            {{ $t('message.dataupload.datasets.conditional.vocabulary') }}
                        </li>
                    </ul>
                </div>
                <div v-if="man" class="d-flex position-relative m-3 w-100">
                    <label class="w-100">
                        <!-- todo: I borrowed this from another input. Maybe refactor? -->
                        {{ $te('message.dataupload.datasets.isReferencedByUrl.placeholder') ? $t('message.dataupload.datasets.isReferencedByUrl.placeholder') : 'Provide an URL' }}
                        <input type="URL" class="selectInputField formkit-inner" placeholder="URL"
                            @input="manURLInput($event)">
                    </label>
                </div>
                <div v-if="vocSearch" class="d-flex position-relative m-3 w-100">
                    <!-- todo: I borrowed this from another input. Maybe refactor? -->
                    <label class="w-100"> {{ $te('message.dataupload.datasets.contributorType.placeholder') ? $t('message.dataupload.datasets.contributorType.placeholder') : 'Choose type of vocabulary' }} 
                        <input id="I2" type="text" class="selectInputField formkit-inner" readonly="readonly" v-model="typeText"
                            :placeholder="$te('message.dataupload.datasets.accessRights.placeholder') ? $t('message.dataupload.datasets.accessRights.placeholder') : 'Choose type of vocabulary'" @click="activeInput('showVocTable');">
                    </label>
                    <ul ref="I2" v-if="showTable.second" class="spatialListUpload">
                        <li v-for="el in listOfVoc" :key="el" class="p-2 border-b border-gray-200 choosableItemsAC"
                            @click=" closeAll(); el.active = !el.active; activeInput('showVocTable'); inputText = ''; voc = el.item; typeText = el.placeholder">
                            {{ $t('message.dataupload.datasets.conditional.' + el.item) }}</li>
                    </ul>
                </div>
            </div>
            <div class="m-3" v-if="vocSearch">
                <div v-for="el in listOfVoc" :key="el" class="position-relative">
                    <label class="w-100" v-if="el.active">
                    <!-- todo: I borrowed this from another input. Maybe refactor? -->
                    <!-- {{ $te('message.dataupload.datasets.accessRights.placeholder') ? $t('message.dataupload.datasets.accessRights.placeholder') : 'Choose type of vocabulary' }}  -->
                    {{ el.placeholder ? el.placeholder : $t('message.dataupload.datasets.conditional.' + el.item) }}
                        <input id="I3" type="text"
                            v-model="inputText" class="selectInputField formkit-inner" :placeholder="$te('message.dataupload.datasets.accessRights.placeholder') ? $t('message.dataupload.datasets.accessRights.placeholder') : 'Choose type of vocabulary'"
                            @click="activeInput('showVocEntries'); inputText = ''"
                        >
                    </label>
                    <ul ref="I3" v-if="showTable.third && el.active" class="spatialListUpload">
                        <li v-for="el in matches" :key="el" class="p-2 border-b border-gray-200 choosableItemsAC"
                            @click="handleSpatielListClick(el)">
                            {{ el.name }}</li>
                    </ul>
                </div>

            </div>

        </div>
    </div>
</template>

<script>

</script>

<style lang="scss" scoped>
.spatialWrap label {
    font-family: var(--fk-font-family-label);
    font-size: var(--fk-font-size-label);
    font-weight: var(--fk-font-weight-label);
    line-height: var(--fk-line-height-label);
}

.spatialWrap {
    .spatialListUpload {
        width: 100%;
        left: 0;
        top: 78px;
    }
}
</style>
