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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Option\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Form
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Option\Edit
 */
class Form extends Generic
{
    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id'      => 'mp_option_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massOption',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setHtmlIdPrefix('option_');
        $form->setFieldNameSuffix('option');

        $fieldset = $form->addFieldset('base_fieldset', ['class' => 'fieldset-wide']);

        $fieldset->addField('product_grid', 'note', [
            'name' => 'product_grid',
            'text' => '<div class="mpmassproductactions_products_grid"></div>'
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        $html = '<div class="mp_option_description_form">';
        $html .= __('Please select the products you want to copy their custom options.');
        $html .= '<br>';
        $html .= __('Only products which include custom options will be displayed as below.');
        $html .= '</div>';
        $html .= parent::getFormHtml();

        return $html;
    }
}
