<template>
  <div>
    <div class="horizontal-wrapper">
      <div v-if="latestIndex > 0" class="d-flex flex-column flex-sm-row mt-6">
        <button type="button" class="add" @click="increment">+ {{ 'Weiteren Eintrag hinzufügen' }}</button>
        <button type="button" class="remove" @click="decrement" :data-key="key">- {{ 'Letzten Eintrag entfernen' }}</button>
      </div>
      <div v-else class="no-entries">
        <div class="d-flex flex-column align-items-center justify-content-center">
          <div> Keine Einträge vorhanden. </div>
          <div><button type="button" class="add" @click="increment">+ {{ 'Eintrag hinzufügen' }}</button></div>
        </div>
      </div>
    </div>
    <div class="repeatable formkitProperty" :class="[props.context.attrs.identifier]"
      v-for="(key, i) in latestIndex" :key="key">
      <h4 v-if="props.context.attrs.class != undefined && props.context.attrs.class.includes('inDistribution')">{{
        `${$t('message.dataupload.distributions.' + props.context.attrs.identifier + '.label')} ${i+1}` }}</h4>
      <h4 v-else>{{ `${$t('message.dataupload.datasets.' + props.context.attrs.identifier + '.label')} ${i+1}` }}</h4>
      <div class="formkitWrapRepeatable">
        <slot></slot>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  context: Object
})

const counter = ref([])

// Need to handle the data like this. The values seem to take their time while loading into the DOM.
setTimeout(() => {
  if (props.context.value.length === 0) {
    counter.value.push('init')
  }
  else {
    for (let index = 0; index < props.context.value.length; index++) {
      if (props.context.value[index] != null) {
        counter.value.push(props.context.value[index]['@value'])
      }
    }
  }
});

const latestIndex = ref(1);
function decrement() {
  if (latestIndex.value > 0) {
    latestIndex.value--;
  }
}

function increment() {
  latestIndex.value++;
}

watch(() => props.context.value, () => {
  if (props.context.value.length > 0) {
    latestIndex.value = props.context.value.length
  }
}, { immediate: true })

// Pushing a blank to the context object and refreshing the counter
const addItem = (index) => {
  counter.value.push(props.context.value[index]['@value'])

}
// remove Item - ToDo need to make sure the localhost notices the splice
const removeItem = (index, counterLength) => {

  if (counterLength != 0) {
    counter.value.splice(index, 1)
  }

}
</script>
<style scoped>
.no-entries {
  width: 100%;
  height: 16rem;
  display: grid;
  place-items: center;
}

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
