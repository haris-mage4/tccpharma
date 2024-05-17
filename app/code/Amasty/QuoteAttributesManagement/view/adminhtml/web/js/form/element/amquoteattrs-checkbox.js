define([
    'underscore',
    'Magento_Ui/js/form/element/single-checkbox'
], function (_, Checkbox) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            inputCheckBoxName: '',
            prefixElementName: ''
        },

        /**
         * @inheritdoc
         */
        initConfig: function () {
            this._super();
            this.configureDataScope();

            return this;
        },

        /**
         * Configure data scope.
         * @returns {void}
         */
        configureDataScope: function () {
            var rowId = this.parentName.split('.').last(),
                elementValue;

            elementValue = this.prefixElementName + rowId;
            this.dataScope = 'data.' + this.inputCheckBoxName;
            this.inputName = this.getInputName(this.inputCheckBoxName);
            this.initialValue = elementValue;
            this.links.value = this.provider + ':' + this.dataScope;
        },

        /**
         * Get checkbox input name
         *
         * @param {String} elementName
         * @returns {String}
         */
        getInputName: function (elementName) {
            var elementNameArray,
                inputName;

            elementNameArray = elementName.split('.');

            inputName = elementNameArray.shift();
            inputName += elementNameArray.reduce(function (prev, curr) {
                return prev + '[' + curr + ']';
            }, '');

            return inputName;
        },

        /**
         * Handle checked state changes for checkbox / radio button.
         *
         * @param {Boolean} newChecked
         * @returns {void}
         */
        onCheckedChanged: function (newChecked) {
            if (!_.isArray(this.value())) {
                this.value([ this.value() ]);
            }

            this._super(newChecked);
        }
    });
});
