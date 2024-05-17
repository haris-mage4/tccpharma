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

namespace Mageplaza\MassProductActions\Block\Adminhtml\AttributeSet\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\MassProductActions\Block\Adminhtml\AttributeSet\Edit\Renderer\AttributeSet;

/**
 * Class Form
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Comment\Edit
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
                'id'      => 'mp_attribute_set_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massAttributeSet',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setHtmlIdPrefix('attribute_set_');
        $form->setFieldNameSuffix('attribute_set');

        $fieldset = $form->addFieldset('base_fieldset', ['class' => 'fieldset-wide']);

        $fieldset->addField('attribute_set_id', AttributeSet::class, [
            'name'  => 'attribute_set_id',
            'label' => __('Attribute Set'),
            'title' => __('Attribute Set')
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
