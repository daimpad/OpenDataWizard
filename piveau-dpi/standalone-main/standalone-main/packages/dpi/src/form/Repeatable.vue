<template>
  <div class="repeatable formkitProperty" :class="[props.context?.attrs.identifier]"
    v-for="({ item: key, nonce }, repeatableIndex) in counter" :key="`${nonce}`">
    <h4 v-if="!!props.context?.attrs.class && props.context.attrs.class.includes('inDistribution')">
      {{ $t('message.dataupload.distributions.' + props.context.attrs.identifier + '.label') }}</h4>
    <h4 v-else>{{ $t('message.dataupload.datasets.' + props.context?.attrs.identifier + '.label') }}</h4>
    <div class="horizontal-wrapper">
      <div class="repeatableWrap">
        <div class="interactionHeaderRepeatable my-1">
          <i18n-t keypath="message.dataupload.info.repeatable" scope="global" tag="p">
            <template v-slot:add>
              <a class="add" @click="addItem(repeatableIndex)">+ {{ $t('message.dataupload.info.add') }}</a>
            </template>
            <template v-slot:remove>

              <a class="remove" :class="{ disabledRemove: props.context?.value.length === 1 }"
                @click="removeItem(repeatableIndex, counter.length)" :data-key="key">- {{
                  $t('message.dataupload.info.remove') }}</a>
            </template>
          </i18n-t>
        </div>
        <div class="formkitWrapRepeatable">

          <slot></slot>

        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { whenever } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = defineProps({
  context: Object
})

const counter = ref<any[]>([{ value: 'init', nonce: 0 }])
const nonce = ref(0)

whenever(() => props.context?.value, (newValue, oldValue) => {
  // Workaround to prevent infinite recursive loop
  if (JSON.stringify(newValue) === JSON.stringify(oldValue)) {
    return
  }
  if (!newValue || newValue.length === 0) {
    counter.value = [{ value: 'init', nonce: 0 }];
  } else {
    counter.value = newValue.filter(Boolean).map((item: any, idx: number) => ({ value: item['@value'] ?? item.name ?? item, nonce: idx }));
  }

  nonce.value = counter.value.length
}, {
  immediate: false,
});

// Pushing a blank to the context object and refreshing the counter
const addItem = (index: number) => {
  counter.value.push({ item: props.context?.value[index]['@value'], nonce: nonce.value })
  nonce.value += 1
}
// remove Item - ToDo need to make sure the localhost notices the splice
const removeItem = (index: number, counterLength: number) => {

  if (counterLength != 1) {
    counter.value.splice(index, 1)
  }

}
</script>
<style scoped>
.add,
.remove {
  padding: 0.5rem;
  margin: 0.5rem;
  border-radius: 5px;
  transition: all 200ms ease-in-out;
}

.add {
  border: 1px solid lightseagreen;

  &:hover {
    text-decoration: none;
    color: white;
    background-color: lightseagreen;
  }
}

.remove {
  border: 1px solid lightcoral;

  &:hover {
    text-decoration: none;
    color: white;
    background-color: lightcoral;
  }
}

.disabledRemove {
  &:hover {
    text-decoration: none;
    color: white;
    background-color: lightgray;
  }
}
</style>
