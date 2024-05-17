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

namespace Mageplaza\MassProductActions\Block\Adminhtml\CrossSell\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\MassProductActions\Helper\Data as HelperData;
use Mageplaza\MassProductActions\Model\Config\Source\Direction;
use Mageplaza\MassProductActions\Model\Config\Source\RemoveProductEvents;

/**
 * Class Form
 * @package Mageplaza\MassProductActions\Block\Adminhtml\CrossSell\Edit
 */
class Form extends Generic
{
    /**
     * @var Direction
     */
    protected $_direction;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RemoveProductEvents
     */
    protected $_removeProductEvent;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Direction $direction
     * @param HelperData $helperData
     * @param RemoveProductEvents $removeProductEvent
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Direction $direction,
        HelperData $helperData,
        RemoveProductEvents $removeProductEvent,
        array $data = []
    ) {
        $this->_direction          = $direction;
        $this->_helperData         = $helperData;
        $this->_removeProductEvent = $removeProductEvent;

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
                'id'      => 'mp_cross_sell_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massCrossSell',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setHtmlIdPrefix('cross_sell_');
        $form->setFieldNameSuffix('cross_sell');

        $onclickText = 'mpMassProductAction.loadProductsGrid(event,mpCrossSellProductsGridUrl);this.hide();';
        $fieldset    = $form->addFieldset('base_fieldset', ['class' => 'fieldset-wide']);

        $fieldset->addField('action', 'select', [
            'label'  => __('Remove Cross-Sell Products'),
            'title'  => __('Remove Cross-Sell Products'),
            'name'   => 'action',
            'class'  => 'mp_products_input_select',
            'values' => $this->_removeProductEvent->toOptionArray()
        ]);

        $fieldset->addField('remove_products', 'text', [
            'name'        => 'remove_products',
            'label'       => __('Remove Specific Products'),
            'title'       => __('Remove Specific Products'),
            'class'       => 'mp_products_input_text',
            'placeholder' => 'product SKU',
            'note'        => $this->_helperData->getSelectProductHtml($onclickText)
                . $this->_helperData->getSubmitProductHtml($this->getViewFileUrl('images/loader-1.gif'))
        ]);

        $fieldset->addField('cross_sell', 'hidden', [
            'name' => 'cross_sell',
        ]);

        $fieldset->addField('add_products', 'text', [
            'name'        => 'add_products',
            'label'       => __('Add Cross-Sell Product(s)'),
            'title'       => __('Add Cross-Sell Product(s)'),
            'class'       => 'mp_products_input_text',
            'placeholder' => 'product SKU',
            'note'        => $this->_helperData->getSelectProductHtml($onclickText)
                . $this->_helperData->getSubmitProductHtml($this->getViewFileUrl('images/loader-1.gif'))
        ]);

        $fieldset->addField('direction', 'select', [
            'name'   => 'direction',
            'label'  => __('Direction'),
            'title'  => __('Direction'),
            'class'  => 'mp_products_input_select',
            'values' => $this->_direction->toOptionArray(),
            'note'   => __('One-way relation: Products with SKUs above will be added to the Related Product List of the selected ones.') .
                __('Mutual-way relation: The same as one-way, but do one step further: The selected ones are also added to the Related Product List of the products with SKUs above.')
        ]);

        $fieldset->addField('copy_products', 'text', [
            'name'        => 'copy_products',
            'label'       => __('Copy from Product(s)'),
            'title'       => __('Copy from Product(s)'),
            'class'       => 'mp_products_input_text',
            'placeholder' => 'product SKU',
            'note'        => $this->_helperData->getSelectProductHtml($onclickText)
                . $this->_helperData->getSubmitProductHtml($this->getViewFileUrl('images/loader-1.gif'))
        ]);

        $fieldset->addField('product_grid', 'note', [
            'name' => 'product_grid',
            'text' => '<div class="mpmassproductactions_products_grid"></div>'
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(Dependence::class)
                ->addFieldMap('cross_sell_action', 'action')
                ->addFieldMap('cross_sell_remove_products', 'remove_products')
                ->addFieldDependence('remove_products', 'action', RemoveProductEvents::SPECIFIC_PRODUCT)
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        $html = '<div class="mp-massproductactions-message submit-message"></div>';
        $html .= parent::getFormHtml();

        return $html;
    }
}
