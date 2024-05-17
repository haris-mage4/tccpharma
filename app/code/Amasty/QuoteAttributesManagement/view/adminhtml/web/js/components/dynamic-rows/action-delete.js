define([
    'underscore',
    'Magento_Catalog/js/form/element/action-delete',
    'uiRegistry'
], function (_, Element, uiRegistry) {
    'use strict';

    return Element.extend({
        configureDataScope: function () {
            var row = uiRegistry.get(this.parentName),
                recordId,
                prefixName,
                suffixName,
                dataScopeValue;

            if (row.data().option_id) {
                recordId = row.data().option_id;
                this.prefixElementName = '';
            } else {
                recordId = this.parentName.split('.').last();
            }

            prefixName = this.dataScopeToHtmlArray(this.prefixName);
            this.elementName = this.prefixElementName + recordId;

            suffixName = '';

            if (!_.isEmpty(this.suffixName) || _.isNumber(this.suffixName)) {
                suffixName = '[' + this.suffixName + ']';
            }

            this.inputName = prefixName + '[' + this.elementName + ']' + suffixName;

            suffixName = '';

            if (!_.isEmpty(this.suffixName) || _.isNumber(this.suffixName)) {
                suffixName = '.' + this.suffixName;
            }

            dataScopeValue = this.prefixName.split('.');
            dataScopeValue.splice(1, 0, this.elementName);

            this.dataScope = 'data.' + dataScopeValue.join('.') + suffixName;

            this.links.value = this.provider + ':data.' + this.prefixName + '.' + this.elementName + suffixName;
        }
    });
});
