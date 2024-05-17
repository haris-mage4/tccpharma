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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\MassProductActions\Block\Adminhtml\Category\Edit\Renderer\AddCategory;
use Mageplaza\MassProductActions\Block\Adminhtml\Category\Edit\Renderer\RemoveCategory;

/**
 * Class Form
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Category\Edit
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
                'id'      => 'mp_category_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massCategory',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setHtmlIdPrefix('category_');
        $form->setFieldNameSuffix('category');

        $fieldset = $form->addFieldset('base_fieldset', ['class' => 'fieldset-wide']);

        $fieldset->addField('remove_category_ids', RemoveCategory::class, [
            'name'  => 'remove_category_ids',
            'label' => __('Remove Categories'),
            'title' => __('Remove Categories'),
        ]);

        $fieldset->addField('add_category_ids', AddCategory::class, [
            'name'  => 'add_category_ids',
            'label' => __('Add Categories'),
            'title' => __('Add Categories'),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
