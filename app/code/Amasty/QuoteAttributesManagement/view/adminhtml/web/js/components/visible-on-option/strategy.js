define(function () {
    'use strict';

    return {
        defaults: {
            valuesForOptions: [],
            imports: {
                toggleVisibility: 'amasty_quoteattributes_attribute_form.amasty_quoteattributes_attribute_form.base_fieldset.frontend_input:value'
            },
            isShown: false,
            inverseVisibility: false
        },

        /**
         * Toggle visibility state.
         *
         * @param {Number} selected
         * @returns {void}
         */
        toggleVisibility: function (selected) {
            this.isShown = selected in this.valuesForOptions;
            this.visible(this.inverseVisibility ? !this.isShown : this.isShown);
        }
    };
});
