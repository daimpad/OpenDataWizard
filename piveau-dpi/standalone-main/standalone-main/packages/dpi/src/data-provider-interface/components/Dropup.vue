<template>
  <div class="btn-group dropup" v-if="(show && !isCatalog) || (show && isCatalog && isOperator)">
    <button type="button" class="dpi-menu-dropup-btn btn btn-link dropdown-toggle" data-bs-toggle="dropdown"
      aria-haspopup="true" aria-expanded="false">
      {{ $t('message.dataupload.menu.'+ groupName ) }}<span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
      <li v-for="item in groupItems" :key="item.key" :data-cy="item.key">
        <!-- Menu items are either buttons or router-link -->
        <!-- depending if they have a 'to' or 'handler' property -->
        <component :is="item.handler ? 'a' : 'router-link'" class="dropdown-item" :class="{ 'disabled': item.disabled }"
          :to="item.to || { name: 'Datasets' }" :href="item.handler ? '#' : ''"
          @click="item.handler ? item.handler() : null">
          {{ $t('message.dataupload.menu.'+ item.name )  }}
        </component>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'Dropup',
  props: {
    groupName: {
      type: String,
    },
    groupItems: {
      type: Array,
    },
    show: {
      type: Boolean,
    },
    isCatalog: {
      type: Boolean,
    },
    isOperator: {
      type: Boolean,
    }
  },
};
</script>

<style lang="scss" scoped>
button.dpi-menu-dropup-btn {
  color: white;
}
</style>
