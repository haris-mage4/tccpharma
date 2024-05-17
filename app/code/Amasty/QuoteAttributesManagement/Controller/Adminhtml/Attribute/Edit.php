<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Controller\Adminhtml\Attribute;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_QuoteAttributesManagement::attribute_edit';

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(Context $context, AttributeRepositoryInterface $attributeRepository)
    {
        parent::__construct($context);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return Page
     */
    public function execute(): ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->updateTitles($resultPage);
        return $resultPage;
    }

    private function updateTitles(Page $page): void
    {
        $title = $this->getTitle();
        $page->setActiveMenu('Amasty_QuoteAttributesManagement::attributes');
        $page->addBreadcrumb($title, $title);
        $page->getConfig()->getTitle()->prepend($title);
    }

    private function getTitle(): string
    {
        $attributeId = (int) $this->getRequest()->getParam(AttributeInterface::ATTRIBUTE_ID);
        if ($attributeId) {
            try {
                $title = $this->attributeRepository->getById($attributeId)->getDefaultFrontendLabel();
            } catch (NoSuchEntityException $e) {
                $title = __('Edit Quote Field')->render();
            }
        }

        return $title ?? __('Edit Quote Field')->render();
    }
}
