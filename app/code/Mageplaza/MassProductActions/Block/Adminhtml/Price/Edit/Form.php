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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Price\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\MassProductActions\Helper\Data as HelperData;
use Mageplaza\MassProductActions\Model\Config\Source\Calculation;

/**
 * Class Form
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Price\Edit
 */
class Form extends Generic
{
    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var Calculation
     */
    protected $_calculation;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param FieldFactory $fieldFactory
     * @param Calculation $calculation
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        FieldFactory $fieldFactory,
        Calculation $calculation,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_yesNo        = $yesNo;
        $this->_fieldFactory = $fieldFactory;
        $this->_calculation  = $calculation;
        $this->_helperData   = $helperData;

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
                'id'      => 'mp_price_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massPrice',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setHtmlIdPrefix('price_');

        /** Normal price field set */
        $priceFieldset = $form->addFieldset('price_fieldset', ['class' => 'fieldset-wide']);

        $priceFieldset->addField('price_type', 'select', [
            'name'   => 'price_filter[price][type]',
            'label'  => __('Change Price'),
            'title'  => __('Change Price'),
            'values' => $this->_calculation->toOptionArray()
        ]);

        $priceFieldset->addField('using_cost', 'select', [
            'name'   => 'price_filter[price][using_cost]',
            'label'  => __('Update using Cost'),
            'title'  => __('Update using Cost'),
            'values' => $this->_yesNo->toOptionArray(),
            'note'   => 'If Yes, the updated price is based on the cost.'
        ]);

        $priceFieldset->addField('price_value', 'text', [
            'name'     => 'price[price]',
            'label'    => __('Value'),
            'title'    => __('Value'),
            'required' => true,
            'class'    => 'validate-greater-than-zero'
        ]);

        /** Cost field set */
        $costFieldset = $form->addFieldset('cost_fieldset', ['class' => 'fieldset-wide']);

        $costFieldset->addField('cost_type', 'select', [
            'name'   => 'price_filter[cost][type]',
            'label'  => __('Change Cost'),
            'title'  => __('Change Cost'),
            'values' => $this->_calculation->toOptionArray()
        ]);

        $costFieldset->addField('cost_value', 'text', [
            'name'     => 'price[cost]',
            'label'    => __('Value'),
            'title'    => __('Value'),
            'required' => true,
            'class'    => 'validate-greater-than-zero'
        ]);

        /** Special price field set */
        $specialPriceFieldset = $form->addFieldset('special_price_fieldset', ['class' => 'fieldset-wide']);

        $specialPriceFieldset->addField('special_price_type', 'select', [
            'name'   => 'price_filter[special_price][type]',
            'label'  => __('Special Price'),
            'title'  => __('Special Price'),
            'values' => $this->_calculation->toOptionArray()
        ]);

        $specialPriceFieldset->addField('using_price', 'select', [
            'name'   => 'price_filter[special_price][using_price]',
            'label'  => __('Update using Price'),
            'title'  => __('Update using Price'),
            'values' => $this->_yesNo->toOptionArray(),
            'note'   => 'If Yes, the updated special price is based on the current price.'
        ]);

        $specialPriceFieldset->addField('special_price_value', 'text', [
            'name'     => 'price[special_price]',
            'label'    => __('Value'),
            'title'    => __('Value'),
            'required' => true,
            'class'    => 'validate-greater-than-zero'
        ]);

        $specialPriceFieldset->addField('special_price_from_date', 'date', [
            'name'        => 'price[special_from_date]',
            'label'       => __('Special Price From Date'),
            'title'       => __('Special Price From Date'),
            'date_format' => 'M/d/yyyy',
            'timezone'    => false
        ]);

        $specialPriceFieldset->addField('special_price_to_date', 'date', [
            'name'        => 'price[special_to_date]',
            'label'       => __('Special Price To Date'),
            'title'       => __('Special Price To Date'),
            'date_format' => 'M/d/yyyy',
            'timezone'    => false
        ]);

        /** Tier price field set */
        $tierPriceFieldset = $form->addFieldset('tier_price_fieldset', ['class' => 'fieldset-wide']);

        if ($this->_helperData->versionCompare('2.2.0')) {
            $tierPriceHtml = $this->_layout->createBlock(TierPrice::class)
                ->setTemplate('Mageplaza_MassProductActions::product/tier-price.phtml')->toHtml();
        } else {
            $tierPriceHtml = $this->_layout->createBlock(TierPrice::class)
                ->setTemplate('Mageplaza_MassProductActions::product/tier-price-old.phtml')->toHtml();
        }

        $tierPriceFieldset->addField('tier_price', 'note', [
            'name'  => 'tier_price[price_value]',
            'label' => __('Change Tier Price(s)'),
            'title' => __('Change Tier Price(s)'),
            'text'  => $tierPriceHtml
        ]);

        $form->setUseContainer(true);
        $allOptions = [];
        foreach ($this->_calculation->toOptionArray() as $option) {
            if ($option['value']) {
                $allOptions[] = $option['value'];
            }
        }

        $refUsingFields = $allOptions;
        if (($key = array_search(Calculation::FIXED_VALUE, $refUsingFields)) !== false) {
            unset($refUsingFields[$key]);
        }

        $refUsingFields = implode(',', $refUsingFields);
        $refUsingField  = $this->_fieldFactory->create([
            'fieldData'   => ['value' => $refUsingFields, 'separator' => ','],
            'fieldPrefix' => ''
        ]);
        $refFields      = implode(',', $allOptions);
        $refField       = $this->_fieldFactory->create([
            'fieldData'   => ['value' => $refFields, 'separator' => ','],
            'fieldPrefix' => ''
        ]);
        /** @var Dependence $dependenceBlock */
        $dependenceBlock = $this->getLayout()->createBlock(Dependence::class)
            ->addFieldMap('price_price_type', 'price_type')
            ->addFieldMap('price_using_cost', 'using_cost')
            ->addFieldMap('price_price_value', 'price_value')
            ->addFieldMap('price_cost_type', 'cost_type')
            ->addFieldMap('price_cost_value', 'cost_value')
            ->addFieldMap('price_special_price_type', 'special_price_type')
            ->addFieldMap('price_using_price', 'using_price')
            ->addFieldMap('price_special_price_value', 'special_price_value')
            ->addFieldMap('price_special_price_from_date', 'special_price_from_date')
            ->addFieldMap('price_special_price_to_date', 'special_price_to_date')
            ->addFieldDependence('using_cost', 'price_type', $refUsingField)
            ->addFieldDependence('price_value', 'price_type', $refField)
            ->addFieldDependence('cost_value', 'cost_type', $refField)
            ->addFieldDependence('using_price', 'special_price_type', $refUsingField)
            ->addFieldDependence('special_price_value', 'special_price_type', $refField)
            ->addFieldDependence('special_price_from_date', 'special_price_type', $refField)
            ->addFieldDependence('special_price_to_date', 'special_price_type', $refField);

        $this->setChild('form_after', $dependenceBlock);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
