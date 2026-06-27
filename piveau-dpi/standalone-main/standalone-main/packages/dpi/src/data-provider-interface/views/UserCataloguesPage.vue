<script setup>
import { useStore } from 'vuex';
import { ref, computed, onMounted, } from 'vue';
import AppLink from "../../widgets/AppLink.vue";
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router';
import { getCurrentInstance } from "vue";

import {
  has,
  isNil,
} from 'lodash-es';

const router = useRouter();
const route = useRoute();

let env = getCurrentInstance().appContext.app.config.globalProperties.$env;
const store = useStore();
let filteredCatalogs = ref([])
let userCatIDList = computed(() => store.getters['auth/getUserCatalogIds'])

let filterCatList = async () => {
  let cache;
  await axios
    .get(env.api.baseUrl + 'search?filter=catalogue&limit=1000')
    .then(response => (cache = response))
    .catch((err) => {
      reject(err);
    });

  cache.data.result.results.forEach((e) => {
    if (has(e, 'title') && !isNil(e.title) && has(e, 'id') && !isNil(e.id)) filteredCatalogs.value.push({ title: Object.values(e.title)[0], id: e.id })
  });

  filteredCatalogs.value = filteredCatalogs.value
    .filter(item => userCatIDList.value.includes(item.id))
    .map(item => ({ id: item.id, name: item.title }));
}
const handleMQA = (cat) => {

  router.push({
    name: 'DataProviderInterface-MQASettings',
    params: { id: cat.id },
    query: { locale: route.query.locale }
  }).catch(() => { });
}
const handleEdit = () => {

}
onMounted(async () => {
  filterCatList()
});
</script>
<template>
  <div class="catOverview">
    <div class="d-flex flex-column bg-transparent container-fluid justify-content-between content ">
      <h1 class="small-headline">{{ $t('message.dataupload.info.userCatalogues') }}</h1>
      <p class="m-0 ">{{ $t('message.dataupload.info.userCatDescription') }}</p>
      <!-- <div class="catWrap">
        <div v-for="(catalog, index) in filteredCatalogs" :key="index" class="annifItems ">
          <app-link
            :to="{ name: 'CatalogueDetails', query: { locale: $route.query.locale }, params: { ctlg_id: catalog.id } }">{{
              catalog.name }}</app-link>
        </div>
        <div v-if="filteredCatalogs.length === 0" v-for="(catalog, index) in userCatIDList" :key="index"
          class="annifItems ">
          <app-link
            :to="{ name: 'CatalogueDetails', query: { locale: $route.query.locale }, params: { ctlg_id: catalog } }">{{
              catalog }}</app-link>
        </div>
      </div> -->


  


    

    <table>
    <thead>
      <tr>
        <th>{{ $t('message.metadata.catalog') }} - ID</th>
        <th>{{ $t('message.metadata.description') }}</th>
        <th>{{ $t('message.dataupload.menu.actions') }}</th>
      </tr>
    </thead>

    <tr v-for="(catalog, index) in filteredCatalogs" :key="index">
      <td>

        <app-link
          :to="{ name: 'CatalogueDetails', query: { locale: $route.query.locale }, params: { ctlg_id: catalog.id } }">{{
            catalog.id }}</app-link>

      </td>
      <td>
        <span>{{ catalog.name }}</span>
         
      </td>
      <td>
        <!-- <button type="button" class="btn btn-secondary" @click="handleEdit(id, catalog)">{{
          $t('message.metadata.linkedData') }}</button>
        <button type="button" class="btn btn-secondary" @click="handleEdit(id, catalog)">{{
          $t('message.dataupload.menu.edit') }}</button> -->

        <button type="button" class="btn btn-action" @click="handleMQA(catalog)">Configure MQA report

        </button>

      </td>
    </tr>


   
  </table>

</div>
  </div>

</template>
<style scoped>

table {
margin-top: 50px !important;
margin-left: 0px !important;
}
.catOverview {
  min-height: 60vh;
}

.btn-action {
  border: solid 1px #3f3f3f;
}

.btn-action:hover {
  border: solid 1px #3f3f3f;
  background-color: #3f3f3f;
  color: #fff;
}

.catWrap {
  display: flex;
  flex-wrap: wrap;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid lightgray;
  justify-content: space-between;
}

.catWrap .annifItems {
  background: #ECECEC;
  flex-grow: 1;
  text-align: center;
}

.subline {
  font-size: 12px;
  color: lightgray;

}


th,
td {
  padding: 1rem;

}

tr {
  padding: 1rem;
  border-bottom: 1px solid lightgray;
}

thead {
  border-bottom: 1px solid lightgray;
}
</style>