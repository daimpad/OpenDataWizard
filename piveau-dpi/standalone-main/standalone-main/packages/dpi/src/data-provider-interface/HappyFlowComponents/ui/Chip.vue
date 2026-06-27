<!-- 

*** Props: ***

:text= STRING 
(*** Anything you want - describes the Text for the chip ***)

:data="{ '@value': STRING, 'URI': STRING }" 
(*** Data for the chip ***)

:setup="{ '@type': STRING , '@icon': STRING, '@inTable': BOOLEAN,  '@search': BOOLEAN, '@selected':BOOLEAN  } 
(*** @type : default|select|selected|disabled , @icon : prefix|suffix , '@search': (adds the magnifier icon) true|false, '@selected': (determines if the chip is preselected) true|false  ***)
-->
<template> 

    <div ref="chipWrap" class="dpiV3_chipBorder" @click="handleWrapperClick" v-if="typeof text  !== 'undefined'"
        :class="{ 'dpiV3_focusBorder': isFocusVisible, 'dpiV3_inTableWrap': setup['@inTable'], 'dpiV3_inRaPFindability_outer': setup['@rapfindability'] }">
        <button @click="handleClick(setup['@type'])" type="button" @mousedown.prevent class="dpiV3_chipBody" :class="{
            'dpiV3_chipUnselected': setup['@type'] === 'select',
            'dpiV3_chipSelected': isSelected,
            'dpiV3_chipDefault': setup['@type'] === 'default',
            'dpiV3_chipDisabled': setup['@type'] === 'disabled',
            'dpiV3_specialFocus': isFocusVisible,
            'dpiV3_inTable': setup['@inTable'],
            'dpiV3_chipStatic': setup['@type'] === 'static',
            'dpiV3_inFindability': setup['@findability'],
            'dpiV3_inRaPFindability': setup['@rapfindability']
        }" @focus="onFocus($event)" @blur="onBlur"
            :disabled="setup['@type'] === 'disabled' || setup['@type'] === 'static'">
            <PhMagnifyingGlass v-if="setup['@search'] && setup['@icon'] === 'prefix'" :size="iconSize" />
            <PhX v-if="!setup['@search'] && setup['@icon'] === 'prefix'" :size="iconSize" />
            <span>{{ text }}</span>
            <PhX v-if="!setup['@search'] && setup['@icon'] === 'suffix'" :size="iconSize" />
            <PhMagnifyingGlass v-if="setup['@search'] && setup['@icon'] === 'suffix'" :size="iconSize" />

        </button>

    </div>
</template>
<script setup>
import { ref } from 'vue';
// load Icons
import { PhX, PhMagnifyingGlass } from "@phosphor-icons/vue";

const props = defineProps({
    text: {
        type: String,
        default: ''
    },
    setup: {
        type: Object,
        required: true

    },
    data: {
        type: Object,
        required: true
    }
});
const chipWrap = ref();
let isSelected = ref(props.setup['@selected']);
let isSearch = ref();
let iconSize = ref('16px')

const emit = defineEmits(['click', 'selection-change']);

const handleWrapperClick = (event) => {
  // Stop event from bubbling up to parent components
  event.stopPropagation();
};

const handleClick = (type, e) => {
    if (type != 'disabled' && type != 'default' && type != 'static') {
        chipWrap.value.children[0].classList.toggle('dpiV3_chipSelected')
        isSelected.value = !isSelected.value
        emit('click', props.data);
        emit('selection-change', {
            selected: isSelected.value,
            data: props.data
        });
        if (isSelected.value) {
            for (let index = 0; index < chipWrap.value.children[0].children.length; index++) {
                if (chipWrap.value.children[0].children[index].tagName === 'IMG' && !isSearch.value) {
                    if (isSelected.value) {
                        chipWrap.value.children[0].children[index].src = X
                    } else {
                        chipWrap.value.children[0].children[index].src = XB
                    }
                }
            }
        }
    }
}
if (props.setup['@type'] === 'selected') {
    isSelected.value = true
}
if (props.setup['@search']) {
    isSearch.value = true
}
const isFocusVisible = ref(false);

const onFocus = (e) => {
    isFocusVisible.value = true;
};

const onBlur = () => {
    isFocusVisible.value = false;
};

</script>
<style scoped>
button {
    all: unset;
    margin: 0 !important;
}

button.dpiV3_chipSelected.dpiV3_chipDisabled {
    background: var(--Colour-neutral-Neutral60, #687178);
    color: var(--Colour-neutral-Neutral0, #FFF);

}

button.dpiV3_chipDisabled {
    color: var(--Colour-neutral-Neutral60, #687178);
}

button.dpiV3_chipDisabled {
    cursor: unset;
}

.dpiV3_chipBody {
    white-space: nowrap;
    color: var(--neutral-100, #0B1A25);
    text-align: center;
    font-family: Inter;

    font-size: var(--copy-small-regular-font-size);
    font-style: normal;
    font-weight: var(--copy-small-regular-font-weight);
    line-height: var(--copy-small-regular-line-height);

    display: inline-flex;
    height: 32px;
    padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
    align-items: center;
    gap: var(--Spacing-2, 8px);
    flex-shrink: 0;
    border-radius: var(--Button-Radius, 24px);
    cursor: pointer;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
}

.dpiV3_specialFocus {
    margin: 0;
}

.dpiV3_chipDefault {
    background: var(--blue-80, #D4EDFC);
}

.dpiV3_chipUnselected {
    color: var(--Colour-blue-Blue80, #0172AD);
    border-radius: var(--Button-Radius, 24px);
    border: 1px solid var(--blue-80, #0172AD);

    &:hover {
        color: var(--Colour-blue-Blue100, #003F6F);
        border: 1px solid var(--Colour-blue-Blue100, #003F6F);
    }
}

.dpiV3_chipSelected {
    color: var(--Colour-neutral-Neutral0, #FFF);
    border-radius: var(--Button-Radius, 24px);
    background: var(--blue-80, #0172AD);

    &:hover {
        background: var(--Colour-blue-Blue100, #003F6F);
        color: var(--Colour-neutral-Neutral0, #FFF);
    }
}

.dpiV3_chipDisabled {
    border-radius: var(--Button-Radius, 24px);
    opacity: 0.6;
    border: 1px solid var(--Colour-neutral-Neutral60, #687178);
}

.dpiV3_chipBorder {
    /* margin: 4.35px; */
    padding: 2px;
    border: 2px solid rgba(255, 255, 255, .1);
    left: -4px;
    top: -4px;
}

.dpiV3_focusBorder {

    /* margin: 4.35px; */
    padding: 2px;
    border-radius: var(--Modal-Radius, 32px);
    border: 2px solid var(--Focused, #0196D8);
    left: -4px;
    top: -4px;
}

.dpiV3_inTableWrap {
    line-height: 16px;
}

.dpiV3_chipStatic {
    background: var(--Colour-blue-Blue20, #D4EDFC);
    color: var(--Colour-neutral-Neutral100, #0B1A25);
    cursor: unset !important;
}

.dpiV3_inTable {
    display: inline-flex;
    padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
    align-items: center;
    gap: var(--Spacing-2, 8px);
    height: unset;
    background: var(--Colour-blue-Blue20, #D4EDFC);

    span {
        line-height: var(--copy-mini-regular-font-size);
        text-align: center;
        font-family: Inter;
        font-size: var(--copy-mini-regular-font-size);
        font-style: normal;
        font-weight: var(--copy-mini-regular-font-weight);
    }
}

.dpiV3_inFindability {
    height: unset;
}

.dpiV3_inRaPFindability {
    border-radius: var(--Button-Radius, 24px);
    background: var(--blue-80, #0172AD);


    color: white;
    height: unset;
}

.dpiV3_inRaPFindability_outer {
    margin: 0 0 8px 0;
    padding: 0;
    border: 0;
}
</style>