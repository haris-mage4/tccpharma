<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Controller\Adminhtml\Attribute;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_QuoteAttributesManagement::attribute_edit';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        Action\Context $context,
        AttributeRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        if ($fieldId) {
            try {
                $this->attributeRepository->deleteById($fieldId);
                $this->messageManager->addSuccessMessage(__('The field has been deleted.'));

                return $this->_redirect('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                return $this->_redirect('*/*/edit', ['attribute_id' => $fieldId]);
            }
        }
        return $this->_redirect('*/*/');
    }
}
