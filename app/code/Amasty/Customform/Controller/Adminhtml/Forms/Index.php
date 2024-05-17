<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml\Forms;

class Index extends \Amasty\Customform\Controller\Adminhtml\Form
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Content'), __('Content'));
        $resultPage->addBreadcrumb(__('Manage Custom Forms'), __('Manage Custom Forms'));
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Forms'));
        $this->_getSession()->unsAmCustomFormData();

        return $resultPage;
    }
}
