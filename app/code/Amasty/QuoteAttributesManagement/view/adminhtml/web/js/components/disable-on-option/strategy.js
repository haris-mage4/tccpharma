define(function () {
    'use strict';

    return {
        defaults: {
            valuesForEnable: [],
            disabled: true,
            imports: {
                toggleDisable: 'amasty_quoteattributes_attribute_form.amasty_quoteattributes_attribute_form.base_fieldset.frontend_input:value'
            }
        },

        /**
         * Toggle disabled state.
         *
         * @param {Number} selected
         * @returns {void}
         */
        toggleDisable: function (selected) {
            this.disabled(!(selected in this.valuesForEnable));
        }
    };
});
