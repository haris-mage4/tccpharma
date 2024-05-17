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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Price;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Edit
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Price
 */
class Edit extends Container
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_MassProductActions::widget/form/container.phtml';

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');

        $this->_blockGroup = 'Mageplaza_MassProductActions';
        $this->_controller = 'adminhtml_price';
    }

    /**
     * @return string
     */
    public function getFormId()
    {
        return 'mp_price_edit_form';
    }
}
