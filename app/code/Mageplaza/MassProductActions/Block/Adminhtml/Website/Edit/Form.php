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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Website\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\MassProductActions\Block\Adminhtml\Website\Edit\Renderer\AddWebsite;
use Mageplaza\MassProductActions\Block\Adminhtml\Website\Edit\Renderer\RemoveWebsite;

/**
 * Class Form
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Website\Edit
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
                'id'      => 'mp_website_edit_form',
                'action'  => $this->getUrl(
                    'mpmassproductactions/product/massWebsite',
                    ['form_key' => $this->getFormKey()]
                ),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setHtmlIdPrefix('website_');
        $form->setFieldNameSuffix('website');

        $fieldset = $form->addFieldset('base_fieldset', ['class' => 'fieldset-wide']);

        $fieldset->addField('add_website', AddWebsite::class, [
            'name'  => 'add_website',
            'label' => __('Add Product To Websites'),
            'title' => __('Add Product To Websites'),
        ]);

        $fieldset->addField('remove_website', RemoveWebsite::class, [
            'name'  => 'remove_website',
            'label' => __('Remove Product From Websites'),
            'title' => __('Remove Product From Websites'),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
