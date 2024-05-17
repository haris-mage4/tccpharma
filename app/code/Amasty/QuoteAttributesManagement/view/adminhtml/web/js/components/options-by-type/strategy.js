define([
    'mage/translate'
], function ($t) {
    'use strict';

    return {
        defaults: {
            optionsByType: {},
            imports: {
                toggleOptions: 'amasty_quoteattributes_attribute_form.amasty_quoteattributes_attribute_form.base_fieldset.frontend_input:value'
            }
        },

        /**
         * @param {String} selected
         * @returns {void}
         */
        toggleOptions: function (selected) {
            if (this.optionsByType[selected]) {
                var options = _.clone(this.optionsByType[selected]);
                options.unshift({
                    label: $t('None'),
                    value: ''
                })
                this.setOptions(options);
            }
        }
    };
});
