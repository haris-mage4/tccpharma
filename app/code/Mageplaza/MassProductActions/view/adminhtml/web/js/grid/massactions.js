/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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
    'jquery',
    'mage/translate',
    'underscore',
    'Magento_Ui/js/grid/tree-massactions',
    'uiRegistry',
    'Magento_Ui/js/modal/modal'
], function ($, $t, _, TreeMassactions, registry) {
    'use strict';

    return TreeMassactions.extend({

        /**
         * Default action callback. Sends selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            switch (action.type){
                case 'mp_update_attributes':
                    var attributeForm  = $('#mp-massproductactions-attribute-form'),
                        attributeTitle = $t('Quick Attributes Update');
                    this.processSelection(data);
                    /** Load attribute edit form */
                    mpMassProductAction.initAttributesForm(attributeForm, mpAttributeLoadUrl, '#mp-attribute-form-container', '#mp-attribute-form-store-switcher');

                    this.initPopupForm(attributeForm, attributeTitle);

                    break;

                case 'mp_change_attribute_set':
                    var attributeSetForm  = $('#mp-massproductactions-attribute-set-form'),
                        attributeSetTitle = $t('Change Attribute Set');

                    /** Reset attribute set ui select */
                    var attrUiSelect = registry.get('attributeSet.attribute_set_id');
                    attrUiSelect.reset();
                    this.processSelection(data);
                    this.initPopupForm(attributeSetForm, attributeSetTitle);

                    break;

                case 'mp_update_category':
                    var categoryForm  = $('#mp-massproductactions-category-form'),
                        categoryTitle = $t('Update Category');
                    this.processSelection(data);
                    this.initPopupForm(categoryForm, categoryTitle);

                    /** Reset category ui select */
                    var removeCatUiSelect = registry.get('productRemoveCategory.product_select_remove_category'),
                        addCatUiSelect    = registry.get('productAddCategory.product_select_add_category');
                    removeCatUiSelect.value('');
                    addCatUiSelect.value('');

                    break;

                case 'mp_update_website':
                    var websiteForm  = $('#mp-massproductactions-website-form'),
                        websiteTitle = $t('Update Website');
                    this.processSelection(data);
                    this.initPopupForm(websiteForm, websiteTitle);

                    /** reset popup form */
                    websiteForm.find('form')[0].reset();

                    break;

                case 'mp_update_related_product':
                    var relatedForm  = $('#mp-massproductactions-related-form'),
                        relatedTitle = $t('Update Related Products');
                    this.processSelection(data);
                    this.initPopupForm(relatedForm, relatedTitle);

                    /** reset popup form */
                    this.resetProductLinkForm(relatedForm);

                    break;

                case 'mp_update_up_sell_product':
                    var upSellForm  = $('#mp-massproductactions-up-sell-form'),
                        upSellTitle = $t('Update Up-Sell Products');
                    this.processSelection(data);
                    this.initPopupForm(upSellForm, upSellTitle);

                    /** reset popup form */
                    this.resetProductLinkForm(upSellForm);

                    break;

                case 'mp_update_cross_sell_product':
                    var crossSellForm  = $('#mp-massproductactions-cross-sell-form'),
                        crossSellTitle = $t('Update Cross-Sell Products');
                    this.processSelection(data);
                    this.initPopupForm(crossSellForm, crossSellTitle);

                    /** reset popup form */
                    this.resetProductLinkForm(crossSellForm);

                    break;

                case 'mp_update_custom_options':
                    var optionForm  = $('#mp-massproductactions-option-form'),
                        optionTitle = $t('Copy Custom Options');
                    this.processSelection(data);
                    this.initPopupForm(optionForm, optionTitle);

                    /** Load product grid */
                    mpMassProductAction.initProductGrid(optionForm, mpOptionProductsGridUrl, {});

                    break;

                case 'mp_add_custom_options':
                    var addOptionForm  = $('#mp-massproductactions-add-option-form'),
                        formElement    = $('#option-form .form-inline'),
                        addOptionTitle = $t('Add Custom Options');

                    this.processSelection(data);
                    if (!formElement.length) {
                        mpMassProductAction.initOptionForm();
                    }
                    this.initPopupForm(addOptionForm, addOptionTitle);

                    break;

                case 'mp_remove_custom_options':
                    this.processSelection(data);
                    mpMassProductAction.removeCustomOption();

                    break;

                case 'mp_update_images':
                    var imageForm  = $('#mp-massproductactions-image-form'),
                        imageTitle = $t('Update Images');
                    this.processSelection(data);
                    this.initPopupForm(imageForm, imageTitle);

                    /** reset popup form */
                    this.resetProductLinkForm(imageForm);

                    break;

                case 'mp_update_inventory':
                    var inventoryForm  = $('#mp-massproductactions-inventory-form'),
                        inventoryTitle = $t('Update Inventory');
                    this.processSelection(data);
                    /** Load inventory edit form */
                    mpMassProductAction.initAttributesForm(inventoryForm, mpInventoryLoadUrl, '#mp-inventory-form-container', '#mp-inventory-form-store-switcher');
                    this.initPopupForm(inventoryForm, inventoryTitle);

                    break;

                case 'mp_update_price':
                    var priceForm  = $('#mp-massproductactions-price-form'),
                        priceTitle = $t('Update Price'),
                        tab        = '#mp-price-form-container';
                    priceForm.find(tab).html('');
                    this.processSelection(data);
                    /** Load price edit form */
                    mpMassProductAction.initAttributesForm(priceForm, mpPriceLoadUrl, tab);
                    this.initPopupForm(priceForm, priceTitle);

                    break;

                default:
                    this._super();
            }
        },

        /**
         *  Get selected records
         *
         *  @param {Object} data - Selections data.
         */
        processSelection: function (data) {
            var itemsType  = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});
            window.mpmassproductactions_Selections = selections;
        },

        /**
         * Init the popup form function
         */
        initPopupForm: function (formName, title) {
            formName.modal({
                type: 'slide',
                title: title,
                innerScroll: true,
                modalClass: 'mp-massproductactions-action-box',
                buttons: []
            });
            formName.trigger('openModal');
            $('.action-select-wrap').removeClass('_active');
            $('.action-select-wrap ul').removeClass('_active');
        },

        /**
         * Reset popup form
         */
        resetProductLinkForm: function (formName) {
            formName.find('form')[0].reset();
            formName.find('.admin__field.field.field-remove_products').hide();
            formName.find('.mpmassproductactions_products_grid').html('');
            formName.find('.mp_load_products_grid').show();
            formName.find('.mp_load_products_grid').prop('disabled', false);
            formName.find('.mp_submit_products_grid').hide();
            formName.find('.mp-massproductactions-message.submit-message').html('');

            /** image form field */
            formName.find('.field-product_sku').css('display', 'none');
        }
    });
});
