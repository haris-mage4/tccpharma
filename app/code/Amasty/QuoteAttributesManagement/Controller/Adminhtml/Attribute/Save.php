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
use Amasty\QuoteAttributesManagement\Model\Backend\Attribute\Initialization as AttributeInitialization;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_QuoteAttributesManagement::attribute_edit';

    public const PERSISTENT_NAME = 'amasty_quote_attribute';

    /**
     * @var AttributeInitialization
     */
    private $attributeInitialization;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        AttributeInitialization $attributeInitialization,
        AttributeRepositoryInterface $attributeRepository,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->attributeInitialization = $attributeInitialization;
        $this->attributeRepository = $attributeRepository;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $postData = $this->getRequest()->getPostValue();

        try {
            $attribute = $this->attributeInitialization->execute($postData);
        } catch (InputException $e) {
            $this->dataPersistor->set(self::PERSISTENT_NAME, $postData);
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->getRedirect();
        }

        try {
            $this->attributeRepository->save($attribute);
            $this->messageManager->addSuccessMessage(__('Attribute was saved successfully.'));
            if ($this->getRequest()->getParam('back')) {
                return $this->getRedirect('*/*/edit', [
                    AttributeInterface::ATTRIBUTE_ID => $attribute->getAttributeId()
                ]);
            } else {
                return $this->getRedirect('*/*');
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong. Please review the error log.')
            );
            $this->logger->error($e->getMessage());
        }

        $this->dataPersistor->set(self::PERSISTENT_NAME, $postData);

        $params = [];
        if ($attribute->getAttributeId()) {
            $path = '*/*/edit';
            $params[AttributeInterface::ATTRIBUTE_ID] = $attribute->getAttributeId();
        } else {
            $path = '*/*/new';
        }

        return $this->getRedirect($path, $params);
    }

    private function getRedirect(string $path = '', array $params = []): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($path) {
            $redirect->setPath($path, $params);
        } else {
            $redirect->setRefererUrl();
        }

        return $redirect;
    }
}
