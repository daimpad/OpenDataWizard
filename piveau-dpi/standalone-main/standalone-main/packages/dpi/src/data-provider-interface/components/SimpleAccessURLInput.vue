<script setup>

import { FormKit } from '@formkit/vue';

const props = defineProps({
    context: Object
})

let URLValue

const saveUrl = async () => {

    if (this.URLValue.includes('http://') || this.URLValue.includes('https://')) {
        await this.context.node.input({ '@id': this.URLValue})
    }
    else await this.context.node.input({ '@id': 'https://' + this.URLValue })

}
</script>
<template>
    <div class="formkitProperty w-100">
        <FormKit id="aUrlLink" v-model="URLValue" class="selectInputField formkit-inner" type="url" name="@id"
            :help="props.context.help" :placeholder="props.context.attrs.placeholder"
            :validation="props.context.attrs.validationType" @input="saveUrl" />
    </div>
</template>
<style></style>