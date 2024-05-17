define([
    'Magento_Ui/js/form/components/fieldset',
    'Amasty_QuoteAttributesManagement/js/components/visible-on-option/strategy',
    'prototype'
], function (Fieldset, strategy) {
    'use strict';

    return Fieldset.extend(strategy).extend({
        defaults: {
            openOnShow: true
        },

        /**
         * Toggle visibility state.
         * @returns {void}
         */
        toggleVisibility: function () {
            this._super();

            if (this.openOnShow) {
                this.opened(this.inverseVisibility ? !this.isShown : this.isShown);
            }
        }
    });
});
