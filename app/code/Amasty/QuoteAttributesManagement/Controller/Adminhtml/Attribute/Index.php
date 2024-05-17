<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_QuoteAttributesManagement::attributes';

    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->updateTitles($resultPage);

        return $resultPage;
    }

    private function updateTitles(Page $page): void
    {
        $title = __('Custom Quote Fields')->render();
        $page->setActiveMenu('Amasty_QuoteAttributesManagement::attributes')
            ->addBreadcrumb($title, $title);
        $page->getConfig()->getTitle()->prepend($title);
    }
}
