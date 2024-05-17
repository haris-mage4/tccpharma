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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Image\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\MassProductActions\Helper\Data as HelperData;
use Mageplaza\MassProductActions\Model\Config\Source\ImageActions;

/**
 * Class Form
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Image\Edit
 */
class Form extends Generic
{
    /**
     * @var ImageActions
     */
    protected $_imageActions;

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
     * @param ImageActions $imageActions
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ImageActions $imageActions,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_imageActions = $imageActions;
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
                'id'      => 'mp_image_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massImage',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setHtmlIdPrefix('image_');
        $form->setFieldNameSuffix('image');

        $onclickText = 'mpMassProductAction.loadProductsGrid(event,mpImageProductsGridUrl);this.hide();';
        $fieldset    = $form->addFieldset('base_fieldset', ['class' => 'fieldset-wide']);

        $fieldset->addField('action', 'select', [
            'name'   => 'action',
            'label'  => __('Action'),
            'title'  => __('Action'),
            'class'  => 'mp_products_input_select',
            'values' => $this->_imageActions->toOptionArray(),
        ]);

        $fieldset->addField('product_sku', 'text', [
            'name'        => 'product_sku',
            'label'       => __('Products'),
            'title'       => __('Products'),
            'class'       => 'mp_products_input_text',
            'placeholder' => 'product SKU',
            'note'        => $this->_helperData->getSelectProductHtml($onclickText)
                . $this->_helperData->getSubmitProductHtml($this->getViewFileUrl('images/loader-1.gif'))
        ]);

        $fieldset->addField('product_grid', 'note', [
            'name' => 'product_grid',
            'text' => '<div class="mpmassproductactions_products_grid"></div>'
        ]);

        $form->setUseContainer(true);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(Dependence::class)
                ->addFieldMap('image_action', 'action')
                ->addFieldMap('image_product_sku', 'product_sku')
                ->addFieldMap('image_product_grid', 'product_grid')
                ->addFieldDependence('product_sku', 'action', ImageActions::COPY_IMAGES)
                ->addFieldDependence('product_grid', 'action', ImageActions::COPY_IMAGES)
        );

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
