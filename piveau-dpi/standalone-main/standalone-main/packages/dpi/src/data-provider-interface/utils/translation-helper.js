import { has, isObject } from 'lodash-es';
import { useI18n } from 'vue-i18n';

/**
 * Translation of each translatable parameter within the given structure if a translation is available
 * @param {*} propertyDefinition Object containing parameters defining the form and their content
 * @param {String} property String defining which property translation should be used
 */

function translateProperty(propertyDefinition, property, t, te) {

    if (has(propertyDefinition, 'identifier')) { // hidden fields don't need a label and have no identifier
        const translatableParameters = ['label', 'info', 'help', 'placeholder', 'add-label'];
        const propertyName = propertyDefinition.identifier;

        for (let valueIndex = 0; valueIndex < translatableParameters.length; valueIndex += 1) {
            let translation = propertyName;
            const parameter = translatableParameters[valueIndex];

            const translationExsists = te(`message.dataupload.${property}.${propertyName}.${parameter}`);
            const translationExsistsEN = te(`message.dataupload.${property}.${propertyName}.${parameter}`, 'en');

            // Check if translation exists
            if (!has(property, parameter)) {
                if (translationExsists) {
                    translation = t(`message.dataupload.${property}.${propertyName}.${parameter}`);
                } else if (translationExsistsEN) {
                    translation = t(`message.dataupload.${property}.${propertyName}.${parameter}`, 'en');
                } else {
                    translation = parameter;
                }

                const isCustomComponentWithProps = !!propertyDefinition.$cmp
                    && !propertyDefinition.$formkit
                    && isObject(propertyDefinition.props);

                const isSelectControlledGroupCustomComponent = isCustomComponentWithProps
                    && propertyDefinition.$cmp === 'SelectControlledGroup';

                if (isSelectControlledGroupCustomComponent) {
                    propertyDefinition.props[parameter] = translation;
                } else {
                    propertyDefinition[parameter] = translation;
                }

                // if (parameter === "info") {

                //     propertyDefinition['sections-schema'] = { prefix: { $el: 'div', attrs: { class: 'infoI', }, children: [{ $el: 'div', children: translation, attrs: { class: 'tooltipFormkit' } }] } }
                // }
            }

            // Highlight mandatory fields
            if (propertyDefinition.mandatory && parameter === "label") propertyDefinition[parameter] = `${translation}*`
        }
    }
}

/**
 * Recursive translation of propertie parameters including recursive translation of nested properties
 * @param {Object} schema Object containing the forms schema
 * @param {String} property String defining which property translation should be used (datasets/ distribution/ catalogues)
 */
function translate(schema, property, t, te) {
    for (let index = 0; index < schema.length; index += 1) {
        const schemaPropertyValues = schema[index];

        // translation of group forms and their nested properties
        if (has(schemaPropertyValues, 'children')) {
            // group attributes should be translated too
            translateProperty(schemaPropertyValues, property, t, te);
            // translated nested properties
            translate(schemaPropertyValues.children, property, t, te);
            // translation of conditional forms and their nested properties
        } else if (has(schemaPropertyValues, 'data')) {
            // group attributes should be translated too
            translateProperty(schemaPropertyValues, property, t, te);
            // translate nested data
            const dataKeys = Object.keys(schemaPropertyValues.data);
            for (let keyIndex = 0; keyIndex < dataKeys.length; keyIndex += 1) {
                const currentKey = dataKeys[keyIndex];
                translate(schemaPropertyValues.data[currentKey], property, t, te);
            }
            // translation of 'normal' singular form properties
        } else {
            translateProperty(schemaPropertyValues, property, t, te);
        }
    }
}

export default translate;
