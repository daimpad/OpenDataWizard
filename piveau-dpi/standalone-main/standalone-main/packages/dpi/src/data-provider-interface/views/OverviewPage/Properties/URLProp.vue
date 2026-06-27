<template>
  
        <td class="font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <!-- SINGULAR URL -->
        <td v-if="value.type === 'singularURL'">
          
            <app-link :to="data[property]">
                {{ data[property]['@type'] }}
            </app-link>

        </td>

        <!-- MULTI URLs -->
        <td class="d-flex align-items-center" v-if="value.type === 'multiURL' && data[property][0]['@id'] != ''">

            <div v-if="isEditMode">
                <input type="text" v-model="contentOfProp">
            </div>
            <div v-else>
             
                <div v-for="(el, index) in data[property]" :key="index">
     
                    <!-- regular multiple URLs wit ID Notation-->
                    <app-link v-if="showValue(el, '@id')" :to="el['@id']">
                        {{ el['@id'] }}
                    </app-link>
                   
                    <!-- IS USED BY -->
                    <app-link v-if="showValue(el, 'dext:isUsedBy')" :to="el['dext:isUsedBy']">
                        {{ el['dext:isUsedBy'] }}
                    </app-link>
                        <!-- regular multiple URLs as an array -->
                    <app-link v-if="typeof el === 'string'" :to="el" >
                        {{ el }} 
                    </app-link>

                </div>
            </div>
            <!-- <div class="infoI" @click="editProp(property)"></div> -->
        </td>
   
    
</template>

<script>
import { string } from "zod";
import AppLink from "../../../../widgets/AppLink.vue";
import { has, isNil, isEmpty } from 'lodash-es';

export default {
    data() {
        return {
            isEditMode: false,
            contentOfProp: ''
        }

    },
    props: {
        property: String,
        value: Object,
        data: Object,
    },
    components: {
        AppLink,
    },
    methods: {
        showValue(property, value) {
            return has(property, value) && !isNil(property[value]) && !isEmpty(property[value]);
        },
        async editProp(e) {
            if (this.isEditMode) {
                await this.$formkit.get(e).context.node.input([{ '@id': this.contentOfProp }])
            }
            else {
                this.contentOfProp = this.$formkit.get(e).context.value[0]['@id']
            }
            this.isEditMode = !this.isEditMode;

        }
    }
}
</script>
