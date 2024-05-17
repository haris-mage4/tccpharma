define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        initialize: function () {
            this._super();
            this.tryDisable();
        },

        tryDisable: function () {
            if (this.source.get('data.attribute_id')) {
                this.disable(true);
            }
        }
    });
});
