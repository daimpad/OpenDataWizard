<template>
    <tr class="w-100">
        <td class=" font-weight-bold">{{ $t(`${value.label}`) }}:</td>

        <td class="d-flex flex-column">
            {{ data[property].filter(el => el['@language']).length === 0 ?
                data[property].map(el => el['@value'])[0] :
                data[property].filter(el => el['@language'] === dpiLocale).map(el => el['@value'])[0] ||
                $t('message.catalogsAndDatasets.noDescriptionAvailable') }}
        </td>
    </tr>
</template>
<script setup>
const props = defineProps({
    property: Object,
    value: Object,
    data: Object,
    dpiLocale: Object
})

</script>
<style scoped></style>