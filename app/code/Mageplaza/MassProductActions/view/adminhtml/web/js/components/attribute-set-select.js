/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_MassProductActions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'Magento_Catalog/js/components/attribute-set-select'
], function (Select) {
    'use strict';

    return Select.extend({
        /**
         * Change set parameter in save and validate urls of form
         *
         * @param {String|Number} value
         */
        changeFormSubmitUrl: function (value) {
            return this;
        }
    });
});
