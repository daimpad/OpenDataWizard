<template>
  <label :for="context.id" class="w-100">
    <button
      class="btn p-0 w-100 d-flex align-items-center justify-content-between"
      data-cy="collapsible-input-group-button"
      @click.prevent.stop="toggleCollapse"
    >
      <div class="">
        <span class="form-label">{{ context.label }}</span>
        <a v-if="info" class="infoButton"><i class="material-icons" :title="info"
          data-bs-toggle="tooltip"
          data-placement="top">info</i>
        </a>
      </div>
      <i
        v-if="!context.isSubField()"
        class="material-icons">
        {{ isCollapsed ? 'expand_more' : 'expand_less' }}
      </i>
    </button>
  </label>
</template>

<script>
import { mapGetters } from 'vuex';
import {
  has,
  isArray,
  isEmpty,
} from 'lodash';
const COLLAPSED_CLASS_NAME = 'collapsed';

export default {
  data: () => ({
    isCollapsed: false,
  }),
  props: {
    context: {
      type: Object,
      required: true,
    },
    info: {
      type: [String, Boolean],
      default: false,
    },
    collapsed: {
      // if true, the fieldset will be collapsed by default
      type: Boolean,
      default: false,
    },
  },
  computed: {
    ...mapGetters('dpiStore', [
      'getData',
    ]),
    propertyIsEmpty() {
      let property;
      let data = this.getData(this.$route.params.property);
      if (this.$route.params.property === 'distributions')  data = data[this.$route.params.id];

      property = data[this.context.name];

      if (!isEmpty(property)) {
        if (has(property, 'skos:exactMatch') && has(property['skos:exactMatch'], '@id') && isEmpty(property['skos:exactMatch']['@id'])) return true
        if (has(property, '@language') && has(property, '@value')) return isEmpty(property['@value']);
        if (isArray(property)) {
          return property
          .map(el => {
            if (has(el, '@language') && has(el, '@value') && isEmpty(el['@value'])) return true;
            if (!isEmpty(el)) return false;
            return true;
          })
          .reduce((a,b) => {
            return a && b;
          }, true);
        }
        return false;
      } else return true;
    },
  },
  mounted() {
    if (this.collapsed && this.propertyIsEmpty) {
      this.toggleCollapse();
    }
  },
  methods: {
    toggleCollapse() {
      // Collapse the input fields using DOM manipulation.
      // todo: find a more elegant way to do this using vue.

      const element = this.$el;
      for (const sibling of element.parentNode.children) {
        // Siblings only with the exception of elements with class="formkit-input-help"
        if (sibling !== element && !sibling.classList.contains('formkit-input-help')) {
          // toggle display:none!important on sibling
          sibling.classList.toggle('d-none');
        }
      }
      // add collapse class to the element
      element.parentNode.classList.toggle(COLLAPSED_CLASS_NAME);
      this.isCollapsed = !this.isCollapsed;
    },
  },
};
</script>

<style>
  .collapsed .d-none {
    display: none !important;
  }
</style>

<style scoped>
.infoButton{
  border-radius: 5px;
  cursor: pointer;
}

.infoButton .material-icons {
  font-size: 20px;
  vertical-align: text-bottom;
  margin-left: 5px;
  margin-bottom: 1px;
}

.form-label {
  font-size: 20px;
}
</style>
