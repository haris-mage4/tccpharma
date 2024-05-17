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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Attribute\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\MassProductActions\Model\Config\Source\MultiSelectFilter;
use Mageplaza\MassProductActions\Model\Config\Source\TextFilter;

/**
 * Class Form
 * @method Form setFormExcludedFieldList(array $list)
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Attribute\Edit
 */
class Form extends Generic
{
    /**
     * @var Attribute
     */
    protected $_attributeAction;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var TextFilter
     */
    protected $_textFilter;

    /**
     * @var MultiSelectFilter
     */
    protected $_multiSelectFilter;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Attribute $attributeAction
     * @param ProductFactory $productFactory
     * @param TextFilter $textFilter
     * @param MultiSelectFilter $multiSelectFilter
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Attribute $attributeAction,
        ProductFactory $productFactory,
        TextFilter $textFilter,
        MultiSelectFilter $multiSelectFilter,
        array $data = []
    ) {
        $this->_attributeAction   = $attributeAction;
        $this->_productFactory    = $productFactory;
        $this->_textFilter        = $textFilter;
        $this->_multiSelectFilter = $multiSelectFilter;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $this->setFormExcludedFieldList([
            'category_ids',
            'gallery',
            'image',
            'media_gallery',
            'quantity_and_stock_status',
            'tier_price',
            'mp_tier_group',
            'mp_specific_customer',
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'msrp',
            'msrp_display_actual_price_type',
            'cost'
        ]);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id'      => 'mp_attribute_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massAttribute',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setFieldNameSuffix('attributes');
        $selectionFieldset = $form->addFieldset('select_attribute_fieldset', ['legend' => __('Select Attribute(s)')]);

        $selectionFieldset->addField('mp_attribute_select', 'note', [
            'name' => 'mp_attribute_select',
            'text' => $this->_getAttributeSelectionHtml($this->getFormExcludedFieldList())
        ]);

        $fieldset   = $form->addFieldset('base_fieldset', ['class' => 'fieldset-wide']);
        $attributes = $this->getAttributes();

        $form->setDataObject($this->_productFactory->create());
        $this->_setFieldset($attributes, $fieldset, $this->getFormExcludedFieldList());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve attributes for product mass update
     *
     * @return DataObject[]
     */
    public function getAttributes()
    {
        return $this->_attributeAction->getAttributes()->getItems();
    }

    /**
     * Additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return [
            'price'   => Price::class,
            'weight'  => Weight::class,
            'image'   => Image::class,
            'boolean' => Boolean::class
        ];
    }

    /**
     * Custom additional element html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        $elementId = $element->getId();
        /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
        $element->addCustomAttribute('disabled', true);
        $element->addClass('mp-attribute-value');
        $html = '';
        if ($elementId === 'weight') {
            $html .= <<<HTML
                <script>require(['Magento_Catalog/js/product/weight-handler'], function (weightHandle) {
                    weightHandle.hideWeightSwitcher();
                });</script>
HTML;
        }
        $attribute     = $element->getEntityAttribute();
        $attributeType = $attribute->getFrontend()->getInputType();

        if ($attributeType === 'text' || $attributeType === 'textarea') {
            $html .= $this->_getAttributeInputFilterHtml(
                $elementId,
                $this->_textFilter->toOptionArray(),
                'text_filter'
            );
        }

        if ($attributeType === 'multiselect') {
            $html .= $this->_getAttributeInputFilterHtml(
                $elementId,
                $this->_multiSelectFilter->toOptionArray(),
                'multiselect_filter'
            );
        }

        return $html;
    }

    /**
     * @param array $exclude
     *
     * @return string
     */
    protected function _getAttributeSelectionHtml($exclude)
    {
        $html = '';
        foreach ($this->getAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            if (!$this->_isAttributeVisible($attribute)) {
                continue;
            }
            if (($inputType = $attribute->getFrontend()->getInputType())
                && ($inputType !== 'media_image' || $attribute->getAttributeCode() === 'image')
                && !in_array($attribute->getAttributeCode(), $exclude, true)
            ) {
                $html .= '<div class="mp-massproductactions-attribute-item col-mp mp-3">
                         <input type="checkbox" id="mp-select-attribute-' . $attribute->getId() . '"
                         data-attr-code="' . $attribute->getAttributeCode() . '"
                         onchange="mpMassProductAction.showAttributeField(event)">
                         <label for="mp-select-attribute-' . $attribute->getId() . '">'
                    . $attribute->getFrontend()->getLocalizedLabel() . '</label>
                    </div>';
            }
        }

        return $html;
    }

    /**
     * @param string $elementId
     * @param array $filters
     * @param string $filterName
     *
     * @return string
     */
    protected function _getAttributeInputFilterHtml($elementId, $filters, $filterName)
    {
        $onchange = ($filterName === 'text_filter')
            ? 'onchange="mpMassProductAction.applyAttributeTextFilter(event)"' : '';
        $html     = '<div class="admin__field-filter-control control"><select id="mp-' . $elementId . '-multiselect-filter"
                        class="mp-attribute-filter select admin__control-select"
                        name="attributes_filter[' . $elementId . ']" ' . $onchange . ' disabled>';
        foreach ($filters as $filter) {
            $html .= '<option value="' . $filter['value'] . '">' . $filter['label'] . '</option>';
        }
        $html .= '</select></div>';

        return $html;
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= <<<HTML
                <script>require(['jquery'], function ($) {
                   $('#mp-massproductactions-attribute-form #base_fieldset input[type=hidden]').prop('disabled',true);
                });</script>
HTML;

        return $html;
    }
}
