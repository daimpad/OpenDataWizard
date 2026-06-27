<!-- LANGUAGE SELECTOR -->
<template>
    <span class="d-inline-block">
        <select v-model="locale">
            <option v-for="lang in Object.keys(languages)" :key="lang" :value="lang">{{ languages[lang] }}</option>
        </select>
    </span>
</template>
  
  <script>
  import { useVModel } from '@vueuse/core';
import { defineComponent } from 'vue';

  export default defineComponent({
    name: 'LanguageSelector',
    data() {
      return {
        languages: {
          bg: 'Български',
          cs: 'čeština',
          da: 'dansk',
          de: 'Deutsch',
          el: 'Ελληνικά',
          en: 'English',
          es: 'español',
          et: 'eesti',
          fr: 'français',
          ga: 'Gaeilge',
          hr: 'Hrvatski',
          hu: 'Magyar',
          it: 'italiano',
          lt: 'lietuvių',
          lv: 'latviešu',
          mt: 'Malti',
          nl: 'Nederlands',
          pl: 'polski',
          pt: 'português',
          ro: 'română',
          sk: 'slovenčina',
          sl: 'slovenščina',
          fi: 'suomi',
          sv: 'svenska',
        },
      };
    },
    props: {
      modelValue: {
        type: String,
        default: 'en',
      },
    },
    created() {},
    setup(props, { emit }) {
      const locale = useVModel(props, 'modelValue', emit, { passive: true });

      return { locale }
    }
  });
  </script>
  
  <style lang="scss" scoped>
  </style>
  