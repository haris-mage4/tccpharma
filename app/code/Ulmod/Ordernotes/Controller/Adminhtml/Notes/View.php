<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\Ordernotes\Controller\Adminhtml\Notes;

use Ulmod\Ordernotes\Controller\Adminhtml\Notes;

/**
 * Notes view controller
 */
class View extends Notes
{
    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
