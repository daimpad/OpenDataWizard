<template>
    <div class="toastBody" :class="{
        'error': props.type === 'error',
        'warning': props.type === 'warning',
        'info': props.type === 'info',
        'success': props.type === 'success',
    }">
        <div class="imgTextWrap">
            <img :src="src" alt="">
            <span>{{ props.text}}</span>
        </div>
        <div class="btnWrapToast">
            <TextButtonSmall @click="$emit('button-clicked')" :buttonText="props.button" style="margin:auto" />
            <CrossOutButton @keydown="handleKeydown" @keyup="handleKeyup" @focus="handleFocus('in')"
                @blur="handleFocus()" :type="'inToast'" class="crossout"
                :class="{ 'pressed': isPressed, 'focused': isFocused }" />
        </div>
    </div>
</template>
<script setup>
import { ref, computed } from 'vue';
import Error from '../img/x-circleFill.svg'
import Info from '../img/InfoFill.svg'
import Success from '../img/CheckCircleFill.svg'
import Warning from '../img/WarningFill.svg'
import TextButtonSmall from "./TextButtonSmall.vue";
import CrossOutButton from "./CrossOutButton.vue";

const src = ref()
let isPressed = ref(false)
let isFocused = ref(false)

const props = defineProps({
    text: {
        type: String,
        required: true
    },
    type: {
        type: String,
        required: true
    },
    button: String,
    action: String
});
const fillSrc = (() => {
    if (props.type === "info") {
        src.value = Info
    }
    if (props.type === "success") {
        src.value = Success
    }
    if (props.type === "warning") {
        src.value = Warning
    }
    if (props.type === "error") {
        src.value = Error
    }

})();
const handleFocus = (action) => {
    if (action === 'in') {
        isFocused.value = true
    } else isFocused.value = false
}
const handleKeydown = (event) => {
    if (event.code === 'Space' || event.code === 'Enter') {

        isPressed.value = true;
        event.preventDefault(); // Verhindert das Standardverhalten
    }
}
const handleKeyup = (event) => {
    if (event.code === 'Space' || event.code === 'Enter') {
        isPressed.value = false;
    }
}
</script>
<style>
.crossout {
    margin: 0 !important;
    display: inline-flex !important;
    padding: var(--Spacing-2, 8px) !important;
    gap: 8px;
    width: auto !important;
    height: auto !important;
}

.focused {
    background: none !important;
}

.pressed {
    background: var(--neutral-20, #E6E7E9) !important;
}

.btnWrapToast {
    gap: var(--Spacing-3, 16px);
}

.imgTextWrap {
    display: flex;
    align-items: center;
    gap: var(--Spacing-2, 8px);
}

.actionWrap {
    display: flex;
    align-items: center;
    gap: var(--Spacing-3, 16px);
}

.toastBody {
    display: flex;
    width: 772px;
    height: 72px;
    padding: var(--Spacing-3, 16px);
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;

    color: var(--neutral-80, #3D4952);

    /* Copy/Copy-Large-SemiBold */
    font-family: Inter;
    font-size: var(--copy-large-semi-bold-font-size);
    font-style: normal;
    font-weight: var(--copy-large-semi-bold-font-weight);
    line-height: var(--copy-large-semi-bold-line-height);

    /* 162.5% */
    div {
        display: flex;
        align-items: center;
    }
}

.error {
    border-radius: var(--Border-Radius, 8px);
    border: 1px solid var(--fill-error, #E53B46);
    background: var(--red-20, #FFEFF0);

    /* Elevation light/3 */
    box-shadow: var(--elevation-light-3)
}

.warning {
    border-radius: var(--Border-Radius, 8px);
    border: 1px solid var(--fill-warning, #FFA741);
    background: var(--orange-20, #FFEEDB);

    /* Elevation light/3 */
    box-shadow: var(--elevation-light-3)
}

.info {
    border-radius: var(--Border-Radius, 8px);
    border: 1px solid var(--blue-50, #2BAFE9);
    background: var(--blue-20, #D4EDFC);

    /* Elevation light/3 */
    box-shadow: var(--elevation-light-3)
}

.success {
    border-radius: var(--Border-Radius, 8px);
    border: 1px solid var(--fill-success, #70CC44);
    background: var(--green-20, #E8F8E1);

    /* Elevation light/3 */
    box-shadow: var(--elevation-light-3)
}
</style>