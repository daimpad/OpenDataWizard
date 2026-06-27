<template>
    <div>

        <tr class="align-items-center" v-if="isSet">
            <!-- <td class=" font-weight-bold" v-if="value.type !== 'special'">{{ $t(`${value.label}`) }}:</td> -->
            <URIProp v-if="value.type === 'singularURI' || value.type === 'multiURI' || value.type === 'singularURI'"
                :property="property" :value="value" :data="data" :inHeader="inHeader">
            </URIProp>
            <URLProp v-if="value.type === 'singularURL' || value.type === 'multiURL'" :property="property"
                :value="value" :data="data"></URLProp>
            <StringProp v-if="value.type === 'singularString' || value.type === 'multiString'" :property="property"
                :value="value" :data="data" :dpiLocale="dpiLocale"></StringProp>
            <MultilingualProp v-if="value.type === 'multiLingual' " :property="property"
                :value="value" :data="data" :dpiLocale="dpiLocale"></MultilingualProp>

            <!-- SPECIAL -->
            <div class="w-100" v-if="value.type === 'special'">
                <div v-if="property === 'dct:publisher' || property === 'dct:license'">
                    <SpecialProp :property="property" :value="value" :data="data" :dpiLocale="dpiLocale"></SpecialProp>
                </div>
                <div
                    v-else-if="property != 'dct:creator' && property != 'dcat:temporalResolution' && property != 'spdx:checksum'">
                    <div v-for="(elem, index) in data[property]" :key="index">
                        <SpecialProp :property="property" :value="value" :data="elem" :dpiLocale="dpiLocale">
                        </SpecialProp>
                    </div>
                </div>
                <div v-else>
                    <SpecialProp :property="property" :value="value" :data="data[property]" :dpiLocale="dpiLocale">
                    </SpecialProp>
                </div>

            </div>

        </tr>
    </div>
</template>

<script>
import URIProp from './Properties/URIProp.vue';
import URLProp from './Properties/URLProp.vue';
import StringProp from './Properties/StringProp.vue';
import SpecialProp from './Properties/SpecialProp.vue';
import MultilingualProp from './Properties/MultilingualProp.vue';
import generalHelper from '../../utils/general-helper';

import { has, isNil, isEmpty } from 'lodash';

export default {
    components: {
        URIProp,
        URLProp,
        StringProp,
        SpecialProp,
        MultilingualProp
    },
    props: {
        profile: String,
        data: Object,
        property: String,
        value: Object,
        dpiLocale: String,
        distId: Number,
        type: String,
        inHeader: String
    },
    computed: {
        isSet() {
            return generalHelper.propertyHasValue(this.data[this.property]);
        }
    }
}
</script>
