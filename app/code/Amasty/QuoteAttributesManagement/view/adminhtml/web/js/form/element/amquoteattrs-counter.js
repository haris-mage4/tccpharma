/**
 * Counter options field component
 */
define([
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (_, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            listens: {
                '${ $.parentName }:visible': 'onVisibilityUpdate'
            },
            validation: {
                'required-entry': false,
                'validate-number': true,
                'greater-than-equals-to': 1
            }
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            this.observe({
                rowsUpdated: false,
                rows: []
            });

            return this._super();
        },

        /**
         * @param {Boolean} state
         * @returns {void}
         */
        onVisibilityUpdate: function (state) {
            this._setRequired(state);

            if (!this.rowsUpdated()) {
                return;
            }

            if (state) {
                if (this.rows().length) {
                    this._setValid(true);
                } else if (this.value()) {
                    this._setValid(false);
                }
            } else if (this.value() === '') {
                this._setValid(true);
            }
        },

        /**
         * @param {Boolean} state
         * @returns {void}
         */
        _setValid: function (state) {
            this.value(state ? 1 : '');
        },

        /**
         * @param {Boolean} state
         * @returns {void}
         */
        _setRequired: function (state) {
            this.validation['required-entry'] = state;
        },

        /**
         * @param {Array} rows
         * @returns {void}
         */
        onRowsUpdate: _.throttle(function (rows) {
            this.rowsUpdated(true);
            this.rows(rows);
            this._setRequired(true);

            switch (rows.length) {
                case 0:
                    this._setValid(false);
                    break;
                case 1:
                default:
                    this._setValid(true);
                    break;
            }
        }, 300)
    });
});
