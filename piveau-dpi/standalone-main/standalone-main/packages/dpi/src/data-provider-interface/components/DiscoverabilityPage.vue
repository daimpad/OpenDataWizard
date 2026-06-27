<template>

    <div class="dpiV3_findabilityWrap">
        <h4 class="dpiV3_title">Machen Sie Ihren Datensatz leicht auffindbar</h4>
        <span>Um sicherzustellen, dass Ihr Datensatz für andere leicht zu finden ist, wählen Sie die relevanten
            Kategorien aus.</span>
        <div class="dpiV3_findabilityThemesWrap">
            <Chip v-for="(item, index) in URIList" :key="index" :text="item.pref_label.de"
                :data="{ '@value': item.value, URI: item.URI }"
                :setup="{ '@type': 'select', '@inTable': false, '@findability': true }" @click="addTolist(item)">
            </Chip>
        </div>
        <!-- <details v-if="URIList.length > 0">{{ URIList[1] }}</details> -->

        <div class="dpiV3_errormsgWrapper" v-if="chosenItems.find(obj => obj.isValid === false)">
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">Bitte wählen Sie mindestens eine Kategorie aus, bevor Sie
                fortfahren.</span>
        </div>


        <!-- <div class="dpiV3_interactionWrap">
            <TextButtonLarge buttonText="Abbrechen" />
            <div class="dpiV3_actionButtonWrap">
                <ButtonV3 buttonText="Zurück" size="large" variant="secondary" />
                <ButtonV3 @click="onButtonClick" buttonText="Weiter" size="large" />
            </div>

        </div> -->
    </div>
</template>

<script setup>
import Chip from "../HappyFlowComponents/ui/Chip.vue";
import { ref, onMounted, computed } from 'vue';
import { getCurrentInstance } from "vue";
import { getDatasetCategories } from "../HappyFlowComponents/services/dpiV3_apis";
import { PhWarning } from "@phosphor-icons/vue";
import { useDpiContext } from "../composables";

const dpiContext = useDpiContext();
const isEditMode = computed(() => {
    return !!dpiContext.value.edit?.enabled;
});

let URIList = ref([]);
let instance = getCurrentInstance().appContext.app.config.globalProperties.$env;
let chosenItems = ref([{ isValid: 'unset' }])

const secret = ref(['this is a secret'])

const props = defineProps({
    context: Object
})

if (!isEditMode.value) props.context.node.input(chosenItems)

const getDataFromEndpoint = async () => {
    try {
        URIList.value = await getDatasetCategories(instance.api.baseUrl);

        // Need to filter out OP_DATPRO
         URIList.value = URIList.value.filter(item => item.id !== 'OP_DATPRO');
       
        
        // Sortiere die URIList alphabetisch nach dem pref_label.de
        URIList.value.sort((a, b) => {
            return a.pref_label.de.localeCompare(b.pref_label.de);
        });
    } catch (error) {
        console.log(error);
    }
}
const addTolist = (item) => {

    const itemExists = chosenItems.value.find(obj => obj.id === item.id);

    if (itemExists) {
        // Entferne den Eintrag
        chosenItems.value = chosenItems.value.filter(obj => obj.id !== item.id);
    } else {
        // Füge den Eintrag hinzu
        chosenItems.value.push({ id: item.id, uri: item.resource, label: item.pref_label['de'] });
    }
    if (chosenItems.value.length > 1) {
        chosenItems.value.find(obj => obj.isValid = true)

    } else chosenItems.value.find(obj => obj.isValid = false);

    props.context.node.input(chosenItems)
}

onMounted(() => {
    getDataFromEndpoint();

});
</script>

<style scoped>
.dpiV3_actionButtonWrap {
    display: flex;
    align-items: center;
    gap: var(--Spacing-3, 16px);
}

.dpiV3_interactionWrap {
    display: flex;
    justify-content: space-between;
    align-items: center;
    align-self: stretch;
}

.dpiV3_findabilityWrap {
    display: flex;
    /* min-width: 448px;
    max-width: 636px; */
    /* padding: var(--Spacing-5, 32px) var(--Spacing-6, 48px); */
    flex-direction: column;
    align-items: flex-start;
    flex: 1 0 0;
    gap: var(--Spacing-5, 32px);
    align-self: stretch;



    span {
        height: 51px;
        align-self: stretch;
        color: var(--Colour-neutral-Neutral80, #3D4952);

        /* Copy/Copy-Large-Regular */
        font-family: Inter;
        font-size: 16px;
        font-style: normal;
        font-weight: 400;
        line-height: 26px;
        /* 162.5% */
    }
}

.dpiV3_findabilityThemesWrap {
    display: flex;
    align-items: flex-start;
    align-content: flex-start;
    width: 412px;
    /* gap: 8px var(--Spacing-2, 8px); */
    align-self: stretch;
    flex-wrap: wrap;
}

.chipBody {
    height: 0 !important
}

.dpiV3_errormsgWrapper {
  display: flex;
  gap: 6px;
  width: auto;
  position: absolute;
  right: 0;
  bottom: 55px;
  color: var(--text-error, #a9242c);
}

.dpiV3_errormsgWrapper span {
  color: var(--text-error, #a9242c);
  text-align: right;
}
</style>