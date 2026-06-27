<template>
    <div>
        <!-- DISTRIBUTIONS -->
        <div class="w-100 disDetailsWrap">
            <div class="tHeadWrap">
                <p class="">{{ $t('message.dataupload.datasets.distribution.overview.name') }}</p>
                <p class="">{{ $t('message.metadata.format') }}</p>
                <p class="">{{ $t('message.metadata.updated') }}</p>
                <p class="">{{ $t('message.metadata.issued') }}</p>
                <p class="">{{ $t('message.dataupload.info.actions') }}</p>
            </div>
            <div v-for="( distribution, id) in distributionList" :key="'distribution' + id">
                <div class="tdWrap" v-if="id % 2 == 0">
                    <p v-if="distribution['dct:title'] != undefined && distribution['dct:title'].filter(el => el['@language'] === dpiLocale).map(el =>
                        el['@value'])[0]">
                        {{ distribution['dct:title'].filter(el => el['@language'] === dpiLocale).map(el =>
                            el['@value'])[0] }}
                    </p>
                    <p v-else>
                        {{ $t('message.dataupload.datasets.distribution.overview.notitleinthislanguage') }}
                    </p>
                    <p v-if="distribution['dct:format'] != '' || Object.keys(distribution['dct:format']).length != 0">
                        <PropertyEntry profile="distributions" :data="distributionList[id]" property='dct:format'
                            :value="tableProperties['dct:format']" :dpiLocale="dpiLocale" :distId="id" :inHeader="true">
                        </PropertyEntry>
                    </p>
                    <p v-else>
                        {{ $t('message.dataupload.datasets.distribution.overview.noformatprovided') }}
                    </p>
                    <p v-if="new Date(distribution['dct:modified']['@value']).toLocaleDateString(dpiLocale) != 'Invalid Date'">

                        {{ new Date(distribution['dct:modified']['@value']).toLocaleDateString(dpiLocale) }}
                    </p>
                    <p v-else>
                        -
                    </p>
                    <p v-if="new Date(distribution['dct:issued']['@value']).toLocaleDateString(dpiLocale) != 'Invalid Date'">
                        {{ new Date(distribution['dct:issued']['@value']).toLocaleDateString(dpiLocale) }}
                    </p>
                    <p v-else>
                        -
                    </p>
                    <p>
                        <a class="moreDisInfoBtn" @click="unfoldDisDetails(id)">
                            More information
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-chevron-down" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                            </svg>
                        </a>
                    </p>
                </div>
                <div class="tdWrap grey" v-else>
                    <p v-if="distribution['dct:title'] != undefined && distribution['dct:title'].filter(el => el['@language'] === dpiLocale).map(el =>
                        el['@value'])[0]">
                        {{ distribution['dct:title'].filter(el => el['@language'] === dpiLocale).map(el =>
                            el['@value'])[0] }}
                    </p>
                    <p v-else>
                        {{ $t('message.dataupload.datasets.distribution.overview.notitleinthislanguage') }}
                    </p>
                    <p v-if="distribution['dct:format'] != undefined">
                        <PropertyEntry profile="distributions" :data="distributionList[id]" property='dct:format'
                            :value="tableProperties['dct:format']" :dpiLocale="dpiLocale" :distId="id" :inHeader="true">
                        </PropertyEntry>
                    </p>
                    <p v-else>
                        {{ $t('message.dataupload.datasets.distribution.overview.noformatprovided') }}
                    </p>

                    <p v-if="new Date(distribution['dct:modified']['@value']).toLocaleDateString(dpiLocale) != 'Invalid Date'">

                       {{ new Date(distribution['dct:modified']['@value']).toLocaleDateString(dpiLocale) }}
                   </p>
                   <p v-else>
                       -
                   </p>
                   <p v-if="new Date(distribution['dct:issued']['@value']).toLocaleDateString(dpiLocale) != 'Invalid Date'">
                       {{ new Date(distribution['dct:issued']['@value']).toLocaleDateString(dpiLocale) }}
                   </p>
                   <p v-else>
                       -
                   </p>
                    <p>
                        <a class="moreDisInfoBtn" @click="unfoldDisDetails(id)">
                            More information
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-chevron-down" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                            </svg>
                        </a>
                    </p>
                </div>

                <div class="disInfoWrap">
                    <ul class="list list-unstyled" v-if="distributions.length > 0">
                        <li class="disWrapper" :key="`distribution${id + 1}`">
                            <!-- DISTRIBUTIONS FORMAT -->

                            <span class=" mb-3 disDetails">
                                <span class="row">

                                </span>
                                <span class="row bg-light mb-2 ">
                                    <!-- DISTRIBUTIONS ACCESS URL -->
                                    <table class="table table-borderless table-responsive pl-3 bg-light mb-0"
                                        v-if="showValue(distribution, 'dcat:accessURL')">
                                        <tr v-for="( elem, index ) in distribution['dcat:accessURL'] " :key="index">
                                            <td class="font-weight-bold w-25">
                                                {{ $t('message.metadata.accessUrl') }}:
                                            </td>
                                            <td class="w-75">
                                                <a :href="elem['@id']">
                                                    {{ elem['@id'] }}
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="table table-borderless table-responsive pl-3 bg-light">
                                        <div v-for="( value, name, index ) in tableProperties " :key="index">
                                            <PropertyEntry profile="distributions" :data="distributionList[id]"
                                                :property="name" :value="value" :dpiLocale="dpiLocale" :distId="id">
                                            </PropertyEntry>
                                        </div>
                                    </table>
                                </span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import PropertyEntry from './PropertyEntry.vue';
import { has, isNil, isEmpty } from 'lodash';
import { truncate } from '../../../utils/helpers';
import generalHelper from '../../utils/general-helper'


export default {
    props: {
        dpiLocale: String,
        distributions: {
            required: true
        }
    },
    components: {
        PropertyEntry,
    },
    computed: {
        ...mapGetters('dpiStore', [
            'getData',
        ]),
        distributionList() {
            let list = [];

            for (let index = 0; index < this.distributions.length; index++) {
                list.push(generalHelper.mergeNestedObjects(this.distributions[index]))
            }
            return list;
        },
    },
    mounted() { },
    methods: {
        truncate,
        getDistributionFormat(distribution) {

            try {
                return distribution['dct:format']['name'];
            } catch (error) {
                return "No format provided"
            }

        },
        showValue(property, value) {
            return has(property, value) && !isNil(property[value]) && !isEmpty(property[value]);
        },
        unfoldDisDetails(e) {
            document.getElementsByClassName("tdWrap")[e].classList.toggle('dropShdw')
            document.getElementsByClassName("bi-chevron-down")[e].classList.toggle('turnChev')
            document.getElementsByClassName("disInfoWrap")[e].classList.toggle('openDisDetails')
        }
    },
    data() {
        return {
            tableProperties: {
                'dct:format': { type: 'singularURI', voc: 'file-type', label: 'message.metadata.format' },
                'dcat:downloadURL': { type: 'multiURL', voc: '', label: 'message.metadata.downloadUrl' },
                'dcat:accessService': { type: 'special', voc: '', label: 'message.dataupload.distributions.accessService.label' },
                'dct:license': { type: 'special', voc: '', label: 'message.metadata.license' },
                'dct:issued': { type: 'date', voc: '', label: 'message.metadata.created' },
                'dct:modified': { type: 'date', voc: '', label: 'message.metadata.updated' },
                'dct:type': { type: 'singularURI', voc: 'distribution-type', label: 'message.metadata.type' },
                'dcat:mediaType': { type: 'singularURI', voc: 'iana-media-types', label: 'message.metadata.mediaType' },
                'dcatap:availability': { type: 'singularURI', voc: 'planned-availability', label: 'message.metadata.availability' },
                'dcat:byteSize': { type: 'singularString', voc: '', label: 'message.metadata.byteSize' },
                'dcat:packageFormat': { type: 'singularURI', voc: 'iana-media-types', label: 'message.metadata.packageFormat' },
                'dcat:compressFormat': { type: 'singularURI', voc: 'iana-media-types', label: 'message.metadata.compressFormat' },
                'adms:status': { type: 'singularURI', voc: 'dataset-status', label: 'message.metadata.status' },
                'dcat:spatialResolutionInMeters': { type: 'singularString', voc: '', label: 'message.metadata.spatialResolutionInMeters.label' },
                'dcat:temporalResolution': { type: 'special', voc: '', label: 'message.dataupload.datasets.temporalResolution.label' },
                'dct:conformsTo': { type: 'special', voc: '', label: 'message.metadata.conformsTo' },
                'dct:language': { type: 'multiURI', voc: 'language', label: 'message.metadata.languages' },
                'dct:rights': { type: 'singularString', voc: '', label: 'message.metadata.rights' },
                'foaf:page': { type: 'special', voc: '', label: 'message.dataupload.datasets.page.label' },
                'odrl:hasPolicy': { type: 'multiURL', voc: '', label: 'message.metadata.hasPolicy' },
                'spdx:checksum': { type: 'special', voc: 'spdx-checksum-algorithm', label: 'message.metadata.checksum' },
                'dcatde:licenseAttributionByText': { type: 'multiLingual', voc: '', label: 'message.dataupload.distributions.licenseAttributionByText.label' },
            }
        }
    },
}
</script>

<style>
.disDetailsWrap p {
    margin-bottom: 0;
}

.moreDisInfoBtn {
    cursor: pointer;
    transition: all 300ms ease-in-out;
}

.moreDisInfoBtn svg {

    transition: all 300ms ease-in-out;
}

.turnChev {
    transform: scale(-1);
}

.moreDisInfoBtn:hover {
    text-decoration: none;
}

.openDisDetails {
    max-height: 2000px !important;
}


.disDetails {
    width: 95%
}

.disInfoWrap {
    overflow: hidden;

    max-height: 0;
    height: auto;
    position: relative;
    z-index: 0;
    transition: all 500ms ease-in-out;
}

.grey {
    background: rgb(248, 248, 248);
}

.tHeadWrap {
    display: flex;
    padding: 1rem;
    background-color: #f2f4f8;
}

.tHeadWrap p {
    width: 25%;
    font-weight: bold;
}

.tdWrap {
    display: flex;
    padding: 1rem;
    position: relative;
    z-index: 1;
    transition: all 300ms ease-in-out;

}

.dropShdw {
    box-shadow: 0 3px 6px -6px black;
}

.tdWrap p {
    width: 25%;
    display: flex;
    align-items: center;
}
</style>
