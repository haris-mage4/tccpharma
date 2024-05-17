<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Command;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Attribute\Extensions\Stores\SaveHandler as StoresSaveHandler;
use Amasty\QuoteAttributes\Model\ResourceModel\Attribute as AttributeResource;
use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
{
    /**
     * @var AttributeResource
     */
    private $attributeResource;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var StoresSaveHandler
     */
    private $storesSaveHandler;

    public function __construct(
        AttributeResource $attributeResource,
        StoresSaveHandler $storesSaveHandler,
        LoggerInterface $logger
    ) {
        $this->attributeResource = $attributeResource;
        $this->storesSaveHandler = $storesSaveHandler;
        $this->logger = $logger;
    }

    /**
     * @param AttributeInterface|Attribute $attribute
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(AttributeInterface $attribute): void
    {
        try {
            $this->attributeResource->save($attribute);
            $this->storesSaveHandler->execute($attribute);
        } catch (AlreadyExistsException $e) {
            throw new CouldNotSaveException(__('Attribute with the same code already exists'));
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Attribute'), $e);
        }
    }
}
