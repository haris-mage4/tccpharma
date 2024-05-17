<?php
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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Inventory\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Source\Backorders;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Mageplaza\MassProductActions\Helper\Data;
use Mageplaza\MassProductActions\Model\Config\Source\InventoryCalculation;
use Mageplaza\MassProductActions\Model\Config\Source\StockStatus;

/**
 * Class Form
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Inventory\Edit
 */
class Form extends Generic
{
    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var StockConfigurationInterface
     */
    protected $_stockConfiguration;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var Backorders
     */
    protected $_backOrders;

    /**
     * @var StockStatus
     */
    protected $_stockStatus;

    /**
     * @var InventoryCalculation
     */
    protected $_inventoryCalculation;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param StockConfigurationInterface $stockConfiguration
     * @param Manager $moduleManager
     * @param Backorders $backorders
     * @param StockStatus $stockStatus
     * @param InventoryCalculation $inventoryCalculation
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        StockConfigurationInterface $stockConfiguration,
        Manager $moduleManager,
        Backorders $backorders,
        StockStatus $stockStatus,
        InventoryCalculation $inventoryCalculation,
        Data $helperData,
        array $data = []
    ) {
        $this->_yesNo                = $yesNo;
        $this->_stockConfiguration   = $stockConfiguration;
        $this->_moduleManager        = $moduleManager;
        $this->_backOrders           = $backorders;
        $this->_stockStatus          = $stockStatus;
        $this->_inventoryCalculation = $inventoryCalculation;
        $this->helperData            = $helperData;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id'      => 'mp_inventory_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massInventory',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setHtmlIdPrefix('inventory_');
        $form->setFieldNameSuffix('inventory');

        $fieldset = $form->addFieldset('base_fieldset', [
            'class'  => 'fieldset-wide',
            'legend' => __('Advanced Inventory')
        ]);

        $fieldset->addField('manage_stock', 'select', [
            'name'     => 'manage_stock',
            'label'    => __('Manage Stock'),
            'title'    => __('Manage Stock'),
            'class'    => 'mp-inventory-value',
            'disabled' => true,
            'values'   => $this->_yesNo->toOptionArray(),
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml(
            $this->getUseConfigHtml('inventory_use_config_manage_stock', 'use_config_manage_stock')
            . $this->getIsChangeHtml('inventory_manage_stock_checkbox')
        );

        $sourceList = $this->helperData->getSourceList();
        if ($this->helperData->versionCompare('2.3.0') && $sourceList->getSize() > 1) {
            $fieldset->addField('qty', 'text', [
                'name'     => 'qty',
                'label'    => __('Qty By Source'),
                'title'    => __('Qty By Source'),
                'class'    => 'mp-inventory-value-source validate-number',
                'disabled' => true,
                'value'    => $this->getDefaultConfigValue('qty') * 1,
                'required' => true,
                'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
            ])->setAfterElementHtml($this->getInventorySource($sourceList));
        } else {
            $fieldset->addField('qty', 'text', [
                'name'     => 'qty',
                'label'    => __('Qty'),
                'title'    => __('Qty'),
                'class'    => 'mp-inventory-value validate-number',
                'disabled' => true,
                'value'    => $this->getDefaultConfigValue('qty') * 1,
                'required' => true,
                'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
            ])->setAfterElementHtml($this->getIsChangeHtml('inventory_qty_checkbox'))
                ->setBeforeElementHtml($this->getValueCalculationHtml('inventory_calculation_qty', 'qty'));
        }

        $fieldset->addField('min_qty', 'text', [
            'name'     => 'min_qty',
            'label'    => __('Out-of-Stock Threshold'),
            'title'    => __('Out-of-Stock Threshold'),
            'class'    => 'mp-inventory-value validate-number',
            'disabled' => true,
            'value'    => $this->getDefaultConfigValue('min_qty') * 1,
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml($this->getUseConfigHtml('inventory_use_config_min_qty', 'use_config_min_qty') .
            $this->getIsChangeHtml('inventory_min_qty_checkbox'))
            ->setBeforeElementHtml($this->getValueCalculationHtml('inventory_calculation_min_qty', 'min_qty'));

        $fieldset->addField('min_sale_qty', 'text', [
            'name'     => 'min_sale_qty',
            'label'    => __('Minimum Qty Allowed in Shopping Cart'),
            'title'    => __('Minimum Qty Allowed in Shopping Cart'),
            'class'    => 'mp-inventory-value validate-number',
            'disabled' => true,
            'value'    => $this->getDefaultConfigValue('min_sale_qty') * 1,
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml(
            $this->getUseConfigHtml('inventory_use_config_min_sale_qty', 'use_config_min_sale_qty') .
            $this->getIsChangeHtml('inventory_min_sale_qty_checkbox')
        )
            ->setBeforeElementHtml(
                $this->getValueCalculationHtml('inventory_calculation_min_sale_qty', 'min_sale_qty')
            );

        $fieldset->addField('max_sale_qty', 'text', [
            'name'     => 'max_sale_qty',
            'label'    => __('Maximum Qty Allowed in Shopping Cart'),
            'title'    => __('Maximum Qty Allowed in Shopping Cart'),
            'class'    => 'mp-inventory-value validate-number',
            'disabled' => true,
            'value'    => $this->getDefaultConfigValue('max_sale_qty') * 1,
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml(
            $this->getUseConfigHtml('inventory_use_config_max_sale_qty', 'use_config_max_sale_qty') .
            $this->getIsChangeHtml('inventory_max_sale_checkbox')
        )
            ->setBeforeElementHtml(
                $this->getValueCalculationHtml('inventory_calculation_max_sale_qty', 'max_sale_qty')
            );

        $fieldset->addField('is_qty_decimal', 'select', [
            'name'     => 'is_qty_decimal',
            'label'    => __('Qty Uses Decimals'),
            'title'    => __('Qty Uses Decimals'),
            'class'    => 'mp-inventory-value',
            'disabled' => true,
            'values'   => $this->_yesNo->toOptionArray(),
            'value'    => $this->getDefaultConfigValue('is_qty_decimal') === 1 ? 1 : 0,
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml($this->getIsChangeHtml('inventory_is_qty_decimal_checkbox'));

        $fieldset->addField('backorders', 'select', [
            'name'     => 'backorders',
            'label'    => __('Backorders'),
            'title'    => __('Backorders'),
            'class'    => 'mp-inventory-value',
            'disabled' => true,
            'values'   => $this->getBackordersOption(),
            'value'    => $this->getDefaultConfigValue('backorders'),
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml(
            $this->getUseConfigHtml('inventory_use_config_backorders', 'use_config_backorders')
            . $this->getIsChangeHtml('inventory_backorders_checkbox')
        );

        $fieldset->addField('notify_stock_qty', 'text', [
            'name'     => 'notify_stock_qty',
            'label'    => __('Notify for Quantity Below'),
            'title'    => __('Notify for Quantity Below'),
            'disabled' => true,
            'class'    => 'mp-inventory-value validate-number',
            'value'    => $this->getDefaultConfigValue('notify_stock_qty') * 1,
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml(
            $this->getUseConfigHtml('inventory_use_config_notify_stock_qty', 'use_config_notify_stock_qty') .
            $this->getIsChangeHtml('inventory_notify_stock_qty_checkbox')
        )
            ->setBeforeElementHtml(
                $this->getValueCalculationHtml('inventory_calculation_notify_stock_qty', 'notify_stock_qty')
            );

        $fieldset->addField('enable_qty_increments', 'select', [
            'name'     => 'enable_qty_increments',
            'label'    => __('Enable Qty Increments'),
            'title'    => __('Enable Qty Increments'),
            'disabled' => true,
            'class'    => 'mp-inventory-value',
            'values'   => $this->_yesNo->toOptionArray(),
            'value'    => $this->getDefaultConfigValue('enable_qty_increments'),
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml(
            $this->getUseConfigHtml('inventory_use_config_enable_qty_increments', 'use_config_enable_qty_increments')
            . $this->getIsChangeHtml('inventory_enable_qty_increments_checkbox')
        );

        $fieldset->addField('qty_increments', 'text', [
            'name'     => 'qty_increments',
            'label'    => __('Qty Increments'),
            'title'    => __('Qty Increments'),
            'disabled' => true,
            'class'    => 'mp-inventory-value validate-number',
            'value'    => $this->getDefaultConfigValue('qty_increments') * 1,
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml(
            $this->getUseConfigHtml('inventory_qty_increments', 'qty_increments') .
            $this->getIsChangeHtml('inventory_use_config_qty_increments')
        )
            ->setBeforeElementHtml(
                $this->getValueCalculationHtml('inventory_calculation_qty_increments', 'qty_increments')
            );

        $fieldset->addField('is_in_stock', 'select', [
            'name'     => 'is_in_stock',
            'label'    => __('Stock Availability'),
            'title'    => __('Stock Availability'),
            'disabled' => true,
            'class'    => 'mp-inventory-value',
            'values'   => $this->_stockStatus->toOptionArray(),
            'value'    => $this->getDefaultConfigValue('is_in_stock'),
            'note'     => '<div class="field-service" value-scope="' . __('[GLOBAL]') . '"></div>'
        ])->setAfterElementHtml($this->getIsChangeHtml('inventory_stock_availability_checkbox'));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param string $inputId
     * @param string $inputName
     *
     * @return string
     */
    public function getValueCalculationHtml($inputId, $inputName)
    {
        $html = '<div class="field calculation">
                    <select name="inventory[calculation][' . $inputName . ']" id="' . $inputId . '"
                        class="mp-inventory-calculation select admin__control-select" disabled="disabled">';
        foreach ($this->_inventoryCalculation->toOptionArray() as $option) {
            $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
        $html .= '</select></div>';

        return $html;
    }

    /**
     * @param string $inputId
     * @param string $inputName
     *
     * @return string
     */
    public function getUseConfigHtml($inputId, $inputName)
    {
        return '<div class="field choice">
                    <input name="inventory[' . $inputName . ']" type="checkbox"
                        id="' . $inputId . '" class="mp-inventory-use-config" value="1"
                        checked="checked" disabled="disabled"
                        onchange="this.value = this.checked
                        ? 1 : 0;mpMassProductAction.enableInventoryField($(event.target));"/>
                    <label for="' . $inputId . '"
                        class="label"><span>' . __('Use Config Settings') . '</span></label>
                </div>';
    }

    /**
     * @param string $inputId
     *
     * @return string
     */
    public function getIsChangeHtml($inputId)
    {
        return '<div class="field choice">
                    <input type="checkbox" id="' . $inputId . '" onchange="this.value = this.checked
                    ? 1 : 0;mpMassProductAction.inventoryIsChange(event);"/>
                    <label for="' . $inputId . '"
                    class="label"><span>' . __('Change') . '</span></label>
                </div>';
    }

    /**
     * @param $sourceList
     *
     * @return string
     */
    public function getInventorySource($sourceList)
    {
        $html  = '<table class="admin__dynamic-rows data-grid" data-role="grid"><thead><tr>';
        $html .= '<th class="data-grid-th">' . __('Name') . '</th>';
        $html .= '<th class="data-grid-th">' . __('Source Status') . '</th>';
        $html .= '<th class="data-grid-th">' . __('Source Item Status') . '</th>';
        $html .= '<th class="data-grid-th">' . __('Qty') . '</th>';
        $html .= '<th class="data-grid-th">' . __('Notify Qty') . '</th>';
        $html .= '<th class="data-grid-th">' . __('Actions') . '</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($sourceList as $source) {
            $html .= '<tr>';
            $html .= '<td>' . $source->getName() . '</td>';
            $html .= '<td>' . ($source->getEnabled() ? __('Enable') : __('Disable'))
                . '<input class="admin__control-text" type="hidden" name="inventory[source]['
                . $source->getSourceCode() . '][status]" /></td>';
            $html .= '<td><select class="admin__control-select" name="inventory[source][' . $source->getSourceCode()
                . '][status]"><option data-title="' . __('In Stock')
                . '" value="1">' . __('In Stock')
                . '</option><option data-title="' . __('Out of Stock')
                . '" value="0">' . __('Out of Stock')
                . '</option></select></td>';
            $html .= '<td><input class="admin__control-text" type="number" name="inventory[source]['
                . $source->getSourceCode() . '][quantity]" /></td>';
            $html .= '<td><input class="admin__control-text" type="number" name="inventory[source]['
                . $source->getSourceCode() . '][notify_stock_qty]" />';
            $html .= '<div class="admin__field"><div class="admin__field admin__field-option">'
                .'<input id="notify_qty_use_default_' . $source->getSourceCode() . '" type="checkbox"'
                . ' class="admin__control-checkbox" name="inventory[source][' . $source->getSourceCode()
                . '][notify_qty_use_default]" /><label for="notify_qty_use_default_'
                . $source->getSourceCode() . '" class="admin__field-label">'
                . __('Use Default') . '</label></div></div>';
            $html .= '</td>';
            $html .= '<td><input id="unassign-' . $source->getSourceCode()
                . '" type="checkbox" name="inventory[source]['
                . $source->getSourceCode() . '][unassign]" /><span for="unassign-' . $source->getSourceCode()
                . '" class="label">' . __('Unassign') . '</span></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * @param string $field
     *
     * @return string|null
     */
    public function getDefaultConfigValue($field)
    {
        return $this->_stockConfiguration->getDefaultConfigValue($field);
    }

    /**
     * @return array
     */
    public function getBackordersOption()
    {
        if ($this->_moduleManager->isEnabled('Magento_CatalogInventory')) {
            return $this->_backOrders->toOptionArray();
        }

        return [];
    }
}
