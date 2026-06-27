<template>
    <!-- CREATOR -->

    <tr v-if="property === 'dct:creator'" class="marginBot">

        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>
            <div v-if="showValue(data, 'rdf:type')">{{ $t('message.metadata.type') }}: {{ data['rdf:type'].split(':')[1]
                }}
            </div>
            <div v-if="showValue(data, 'foaf:name')">{{ $t('message.metadata.name') }}: {{ data['foaf:name'] }}</div>
            <div v-if="showValue(data, 'foaf:mbox')">{{ $t('message.metadata.email') }}: <app-link
                    :to="`mailto:${data['foaf:mbox']}`">{{ data['foaf:mbox'] }}</app-link></div>
            <div v-if="showValue(data, 'foaf:homepage')">{{ $t('message.metadata.homepage') }}: <app-link
                    :to="data['foaf:homepage']">{{ data['foaf:homepage'] }}</app-link>
            </div>
        </td>

    </tr>

    <!-- CONTACT POINT -->
    <tr v-if="property === 'dcat:contactPoint' && showValue(data, 'rdf:type')">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td class="">
            <div v-if="showValue(data, 'rdf:type')">{{ $t('message.metadata.type') }}: {{ data['rdf:type'].split(':')[1]
                }}
            </div>
            <div v-if="showValue(data, 'vcard:fn')">{{ $t('message.metadata.name') }}: {{ data['vcard:fn'] }}</div>
            <div v-if="showValue(data, 'vcard:hasEmail')">{{ $t('message.metadata.email') }}: <app-link
                    :to="`mailto:${data['vcard:hasEmail']}`">{{ data['vcard:hasEmail'] }}</app-link></div>
            <div v-if="showValue(data, 'vcard:hasOrganizationName')">{{
                data['vcard:hasOrganizationName'] }}</div>
            <div v-if="showValue(data, 'vcard:hasTelephone')">{{ $t('message.metadata.telephone') }}: {{
                data['vcard:hasTelephone'] }}</div>
            <div v-if="showValue(data, 'vcard:hasURL')">{{ $t('message.metadata.url') }}: <app-link
                    :to="data['vcard:hasURL']">{{ data['vcard:hasURL'] }}</app-link></div>
            <div v-if="showValue(data, 'vcard:hasAddress')">
                {{ $t('message.metadata.address') }}:
                <span v-if="showValue(data['vcard:hasAddress'], 'vcard:street_address')">{{
                    data['vcard:hasAddress']['vcard:street_address'] }}</span>,
                <span v-if="showValue(data['vcard:hasAddress'], 'vcard:postal_code')">{{
                    data['vcard:hasAddress']['vcard:postal_code'] }}</span>
                <span v-if="showValue(data['vcard:hasAddress'], 'vcard:locality')">{{
                    data['vcard:hasAddress']['vcard:locality'] }}</span>,
                <span v-if="showValue(data['vcard:hasAddress'], 'vcard:country_name')">{{
                    data['vcard:hasAddress']['vcard:country_name'] }}</span>
            </div>
        </td>
    </tr>
    <!-- CONTRIBUTOR / MAINTAINER / ORIGINATOR-->
    <tr v-if="property === 'dct:contributor' || property === 'dcatde:maintainer' || property === 'dcatde:originator'">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>
            <div v-if="showValue(data, 'rdf:type')">{{ $t('message.metadata.type') }}: {{ data['rdf:type'].split(':')[1]
                }}
            </div>
            <div v-if="showValue(data, 'foaf:name')">{{ $t('message.metadata.name') }}: {{ data['foaf:name'] }}</div>
            <div v-if="showValue(data, 'foaf:mbox')">{{ $t('message.metadata.email') }}: <app-link
                    :to="`mailto:${data['foaf:mbox']}`">{{ data['foaf:mbox'] }}</app-link></div>
            <div v-if="showValue(data, 'foaf:homepage')">{{ $t('message.metadata.homepage') }}: <app-link
                    :to="data['foaf:homepage']">{{ data['foaf:homepage'] }}</app-link>
            </div>
        </td>

    </tr>
    <!-- ADMS IDENTIFIER -->
    <div v-if="property === 'adms:identifier' && checkadms('adms:identifier')" class="d-flex">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>
            <div v-if="showValue(data, '@id') && property === 'adms:identifier'">{{ $t('message.metadata.url') }}:
                <app-link :to="data['@id']">{{ data['@id'] }}</app-link>
            </div>
            <div v-if="showValue(data, 'skos:notation') && showValue(data['skos:notation'][0], '@value')">{{
                $t('message.metadata.identifier') }}: {{ data['skos:notation'][0]['@value'] }}</div>
            <div v-if="showValue(data, 'skos:notation') && showValue(data['skos:notation'][0], '@type')">{{
                $t('message.metadata.type') }}: {{ data['skos:notation'][0]['@type'] }}</div>
        </td>
    </div>

    <!-- TEMPORAL -->
    <tr v-if="property === 'dct:temporal' && showValue(data, 'dcat:startDate')">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td class="d-flex flex-column">
            <div v-if="showValue(data, 'dcat:startDate')"><b>From:</b> {{ new Date(data['dcat:startDate']) }}&nbsp;
            </div>
            <div v-if="showValue(data, 'dcat:endDate')"><b>to:</b> {{ new Date(data['dcat:endDate']) }}
            </div>
        </td>
    </tr>


    <!-- CHECKSUM -->
    <div v-if="property === 'spdx:checksum' && Object.keys(data).length > 0" class="d-flex">
        <td class="font-weight-bold ">{{ $t(`${value.label}`) }}:</td>
        <td class="">
            <div v-if="typeof data === 'string'">{{ data }}</div>
            <div v-if="typeof data === 'object'">{{ data['spdx:checksumValue'] }}</div>
            <div v-if="typeof data === 'object'">{{ data['spdx:algorithm']['name'] }}</div>
        </td>
    </div>

    <!-- PAGE -->
    <div v-if="property === 'foaf:page'" class="w-100 d-flex">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>
            <div v-if="showMultilingualValue(data, 'dct:title')">{{ $t('message.metadata.title') }}: {{
                data['dct:title'].filter(el => el['@language']).length === 0 ?
                    data['dct:title'].map(el => el['@value'])[0] :
                    data['dct:title'].filter(el => el['@language'] === dpiLocale).map(el => el['@value'])[0] ||
                    $t('message.dataupload.datasets.distribution.overview.notitleinthislanguage')
            }}
            </div>
            <!-- <div v-if="showMultilingualValue(data, 'dct:title')" class="multilang">This property is available in: <span -->
            <!-- v-for="(el, index) in data['dct:title']" :key="index">({{ el['@language'] }}) </span></div> -->
            <div v-if="showMultilingualValue(data, 'dct:description')">{{ $t('message.metadata.description') }}: {{
                data['dct:description'].filter(el => el['@language']).length === 0 ?
                    data['dct:description'].map(el => el['@value'])[0] :
                    data['dct:description'].filter(el => el['@language'] === dpiLocale).map(el => el['@value'])[0] ||
                    $t('message.catalogsAndDatasets.noDescriptionAvailable') }}</div>
            <!-- <div v-if="showMultilingualValue(data, 'dct:description')" class="multilang">This property is available in: -->
            <!-- <span v-for="(el, index) in data['dct:description']" :key="index">({{ el['@language'] }}) </span></div> -->
            <div v-if="showValue(data, 'dct:format')">{{ $t('message.metadata.format') }}: {{ data['dct:format']['name']
                }}
            </div>
            <div v-if="showValue(data, '@id')">{{ $t('message.metadata.url') }}: <app-link :to="data['@id']">{{
                data['@id']
                    }}</app-link></div>
        </td>
    </div>

    <!-- CONFORMS TO -->
    <div v-if="property === 'dct:conformsTo' && showValue(data, 'rdfs:label')" class="w-100 d-flex">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>
            <div v-if="showValue(data, 'rdfs:label')">{{ data['rdfs:label'] }}</div>
            <app-link v-if="showValue(data, '@id')" :to="data['@id']">{{ data['@id'] }}</app-link>
        </td>
    </div>

    <!-- TEMPORAL RESOLUTION -->
    <tr v-if="property === 'dcat:temporalResolution'">
        <td class=" flex-column font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>
            <div>{{ convertTemporalResolution(data) }}</div>
        </td>
    </tr>
    <!-- DATA SERVICE -->
    <tr v-if="data['rdf:type'] === 'dcat:DataService'">
     
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td class="">
            <div v-if="showValue(data, 'dct:title')">
                <span class="">{{ $t('message.dataupload.distributions.accessServiceTitle.label')
                    }}:</span>
                {{ data['dct:title'].filter(el => el['@language'] === dpiLocale).map(el => el['@value'])[0] }}
                <span
                    v-if="data['dct:title'].filter(el => el['@language'] === dpiLocale).map(el => el['@value'])[0] === undefined"><b>{{
                        $t('message.dataupload.datasets.distribution.overview.notitleinthislanguage') }}</b></span>
            </div>
            <div v-if="showValue(data, 'dct:description')">
                <span class="">{{ $t('message.dataupload.distributions.accessServiceDescription.label')
                    }}:</span>
                {{ data['dct:description'].filter(el => el['@language'] === dpiLocale).map(el => el['@value'])[0] }}
                <span
                    v-if="data['dct:description'].filter(el => el['@language'] === dpiLocale).map(el => el['@value'])[0] === undefined"><b>{{
                        $t('message.dataupload.datasets.distribution.overview.nodescriptioninthislanguage')
                        }}</b></span>
            </div>
            <div v-if="showValue(data, 'dcat:endpointURL')" class="pr-1">
                <span class="">{{ $t('message.dataupload.distributions.accessServiceEndpointURL.label')
                    }}:</span>
                <app-link class="w-100" :to="data['dcat:endpointURL']">{{ data['dcat:endpointURL'] }}</app-link>
            </div>
        </td>


    </tr>
    <!-- PUBLISHER -->
    <tr v-if="value.isHeader && manualSwitch(data, value.isHeader) === 'man'">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>{{ data['dct:publisher']['foaf:name'] }}</td>
    </tr>
    <tr v-if="manualSwitch(data, value.isHeader) === 'man' && !value.isHeader">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>
            <div v-for="item, index in Object.keys(data['dct:publisher']) ">
                <div
                    v-if="data['dct:publisher'][item] != null && data['dct:publisher'][item] != '' && item === 'foaf:name'">
                    <span class="">{{
                        $t('message.dataupload.datasets.publisherName.label') }}:</span>
                    <span>{{ data['dct:publisher'][item] }}</span>
                </div>
                <div
                    v-if="data['dct:publisher'][item] != null && data['dct:publisher'][item] != '' && item === 'foaf:mbox'">
                    <span class="">{{
                        $t('message.dataupload.datasets.publisherEmail.label') }}:</span>
                    <app-link class="w-100" :to="item">{{ data['dct:publisher'][item] }}</app-link>
                </div>
                <div
                    v-if="data['dct:publisher'][item] != null && data['dct:publisher'][item] != '' && item === 'foaf:homepage'">
                    <span class="">{{
                        $t('message.dataupload.datasets.publisherHomepage.label') }}:</span>
                    <app-link class="w-100" :to="item">{{
                        data['dct:publisher'][item] }}</app-link>
                </div>
            </div>
        </td>
    </tr>
    <tr v-if="manualSwitch(data) === 'auto'">
        <URIProp :property="property" :value="value" :data="data">
        </URIProp>
    </tr>
    <!-- License -->

    <tr v-if="manualSwitch(data) === 'liMan'">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>
        <td>
            <div v-for="item, index in Object.keys(data['dct:license']) ">
                <div
                    v-if="data['dct:license'][item] != null && data['dct:license'][item] != '' && item === 'dct:title'">
                    <span class="">{{
                        $t('message.dataupload.distributions.licenceTitle.label') }}:</span>
                    <span>{{ data['dct:license'][item] }}</span>
                </div>
                <div
                    v-if="data['dct:license'][item] != null && data['dct:license'][item] != '' && item === 'skos:prefLabel'">
                    <span class="">{{
                        $t('message.dataupload.distributions.licenceDescription.label') }}:</span>
                    <app-link class="w-100" :to="item">{{ data['dct:license'][item] }}</app-link>
                </div>
                <div
                    v-if="data['dct:license'][item] != null && data['dct:license'][item] != '' && item === 'skos:exactMatch'">
                    <span class="">{{
                        $t('message.dataupload.distributions.licenceURL.label') }}:</span>
                    <app-link class="w-100" :to="item">{{
                        data['dct:license'][item] }}</app-link>
                </div>
            </div>
        </td>
    </tr>
    <tr v-if="manualSwitch(data) === 'liAuto'">
        <URIProp :property="property" :value="value" :data="data">
        </URIProp>
    </tr>
</template>

<script>
import AppLink from "../../../../widgets/AppLink.vue";
import dateFilters from "../../../../filters/dateFilters";
import { has, isNil, isEmpty } from 'lodash-es';
import URIProp from './URIProp.vue';
import { object } from "zod";

export default {
    props: {
        property: String,
        value: Object,
        data: Object,
        dpiLocale: String,
    },
    components: {
        AppLink,
        URIProp,
    },

    methods: {
        // showDataService() {


        //     try {
        //         return this.property === 'dcat:accessService' && Object.keys(this.data[Object.keys(this.data)[0]][0]).length > 1 && Object.keys(this.data[Object.keys(this.data)[1]][0]).length > 1;

        //     } catch (error) {
        //     }
        // },
        manualSwitch(propData, head) {

            if (propData != undefined) {
                if (propData['dct:publisher'] != undefined) {
                    if (typeof propData === 'string') {
                        return false
                    }
                    if (Object.keys(propData['dct:publisher'])[1] != 'resource') {
                        return 'man'
                    }
                    if (Object.keys(propData['dct:publisher'])[0] != 'foaf:name') {
                        return 'auto'
                    }
                    if (head === true) {
                        return 'head'
                    }
                    else return false
                }
                if (propData['dct:license'] != undefined) {
                    if (typeof propData === 'string') {
                        return false
                    }
                    if (Object.keys(propData['dct:license'])[1] != 'resource') {
                        return 'liMan'
                    }
                    if (Object.keys(propData['dct:license'])[0] != 'foaf:name') {
                        return 'liAuto'
                    }
                    else return false
                }

            }


        },
        showMultilingualValue(property, value) {
            if (property[value] != undefined) {
                const nonEmptyProperty = has(property, value) && !isNil(property[value]) && !isEmpty(property[value]);

                // there should only be one value for each language (so only one item within the array)
                const localeValues = property[value].filter(el => el['@language'] === this.dpiLocale).map(el => el['@value']).filter(el => el !== undefined);
                const otherLocaleValues = property[value].filter(el => el['@language'] !== this.dpiLocale).map(el => el['@value']).filter(el => el !== undefined);

                const existingLocalValues = localeValues.length > 0;
                const existingOtherValues = otherLocaleValues.length > 0;

                // if values for other languages are available, that will be noted

                return nonEmptyProperty && (existingLocalValues || existingOtherValues);
            }
            else {
                return ''
            }

        },
        checkadms(str) {
            if (this.property === str) {
                // console.log(this.showValue(this.data, '@id'), this.showValue(this.data, 'skos:notation') );
                return this.showValue(this.data, '@id') && this.showValue(this.data, 'skos:notation')
            }
        },
        showValue(property, value) {
            // console.log(property[value], value, has(property, value) && !isNil(property[value]) && !isEmpty(property[value]) && property[value] !== undefined);
            try {
                return has(property, value) && !isNil(property[value]) && !isEmpty(property[value]) && property[value] !== undefined;
            } catch (error) {

            }
        },
        filterDateFormatEU(date) {
            return dateFilters.formatEU(date);
        },
        convertTemporalResolution(data) {
            const values = {
                "Year": "",
                "Month": "",
                "Day": "",
                "Hour": "",
                "Minute": "",
                "Second": ""
            };

            for (let i = 0; i < Object.keys(values).length; i += 1) {
                const key = Object.keys(values)[i];
                try {
                    if (has(data, key)) {
                        if (key !== 'Year' && data[key].length < 2) {
                            values[key] = `0${data[key]}`;
                        } else if (key === 'Year' && data[key].length < 4) {
                            if (data[key].length === 3) values[key] = `0${data[key]}`;
                            else if (data[key].length === 2) values[key] = `00${data[key]}`;
                            else if (data[key].length === 1) values[key] = `000${data[key]}`;
                        } else {
                            values[key] = data[key];
                        }
                    } else {
                        if (key === "Year") values[key] = "0000";
                        else values[key] = "00";
                    }
                } catch (error) {

                }

            }

            return `${values['Hour']}:${values['Minute']}:${values['Second']} - ${values['Day']}.${values['Month']}.${values['Year']}`
        }
    }

}
</script>
<style>
.dpiSpecialPropWrap div {

    margin-bottom: 0.5rem;

}
</style>
