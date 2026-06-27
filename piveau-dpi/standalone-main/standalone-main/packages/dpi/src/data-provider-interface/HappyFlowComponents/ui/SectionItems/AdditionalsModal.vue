<template>

    <div v-if="!subActive">
        <!-- <h4 class="headline-4">Optionale Informationen hinzufügen</h4> -->
        <hr>
        <outerItemWrapper>
            <headlineWrapper>
                <h5 class="headline-5">Empfohlen</h5>
                <CloseOpenButtonV3 @click="activeSection = 'recommended'" :expanded="activeSection != 'recommended'" />

            </headlineWrapper>
            <itemWrapper v-show="activeSection === 'recommended'">
                <!-- for now there are only 4 entries in here -->
                <div v-for="item in recommended">
                    <span @click="openNewModal(item)" class="copy-large-regular">{{ $t("message.dataupload.datasets." +
                        item.identifier + ".label")
                        }}</span>

                </div>

            </itemWrapper>
        </outerItemWrapper>
        <hr>
        <outerItemWrapper>
            <headlineWrapper>
                <h5 class="headline-5">Fortgeschrittene</h5>
                <CloseOpenButtonV3 @click="activeSection = 'advanced'" :expanded="activeSection != 'advanced'" />
            </headlineWrapper>
            <itemWrapper v-show="activeSection === 'advanced'">
                <!-- The rest of the additionals List -->
                <div v-for="item in advanced">
                    <span @click="openNewModal(item)" class="copy-large-regular">{{ $t("message.dataupload.datasets." +
                        item.identifier + ".label") }}</span>
                </div>
            </itemWrapper>
        </outerItemWrapper>
    </div>
    <div v-if="subActive">
        <h4 class="headline-4">{{ $t("message.dataupload.datasets." + activeSubItem.identifier + ".label") }} hinzufügen
        </h4>
        <AdditionalsSubModal :context="context" :item="activeSubItem" @goBack="recieveBack" @sst="forwardValue">
        </AdditionalsSubModal>
    </div>
</template>
<script setup>
import { ref } from 'vue'
import { useFormSchema } from '../../../composables';
import CloseOpenButtonV3 from "../CloseOpenButtonV3.vue";
import AdditionalsSubModal from "./AdditionalsSubModal.vue";
import { defineEmits } from 'vue';

const emit = defineEmits();
const { getSchema } = useFormSchema()
const schema = ref(getSchema('datasets'))
const subActive = ref(false)
let recommended = ref([])
let advanced = ref([])
let activeSection = ref('recommended')
let activeSubItem = ref()
const receivedData = ref('');
// Filling the arrays on basis of the appearance of the properties in the Schema - just to match the Mockups

const props = defineProps({
    context: Object
})
function forwardValue(value) {
    emit('closeModal', value);
}

for (let index = 0; index < schema.value['Additionals'].length; index++) {
    if (index < 4) {
        recommended.value.push(schema.value['Additionals'][index])
    } else advanced.value.push(schema.value['Additionals'][index])
}

const openNewModal = (item) => {
    activeSubItem.value = item
    subActive.value = true
}
const recieveBack = (data) => {
    subActive.value = data
    receivedData.value = data;
};

</script>

<style scoped>
hr {
    margin: 0 !important;
    max-width: 100vw !important;
    border-top: 1px solid rgb(159, 159, 159) !important;
    width: 111.5% !important;
    margin-left: -6% !important;
}

.headline-4 {
    padding-bottom: var(--Spacing-5, 32px)
}

outerItemWrapper {
    margin-bottom: var(--Spacing-4, 24px);

    headlineWrapper {
        padding: var(--Spacing-4, 24px) var(--Spacing-2, 8px);
        display: flex;
        justify-content: space-between;
        align-items: center;
        align-self: stretch;
    }
}
.seperatorLineRAP{

}
itemWrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    align-self: stretch;
    display: flex;

    div {
        border-top: 1px solid var(--neutral-20, #E6E7E9);
        display: flex;
        padding: var(--Spacing-3, 16px) var(--Spacing-2, 8px) var(--Spacing-3, 16px) var(--Spacing-2, 8px);
        flex-direction: column;
        justify-content: center;
        align-items: center;
        /* gap: var(--Spacing-3, 16px); */
        align-self: stretch;

        &:hover {
            background: var(--neutral-20, #E6E7E9);
            cursor: pointer;
        }

        span {
            align-self: stretch;
            color: var(--neutral-80, #3D4952);

        }
    }
}
</style>