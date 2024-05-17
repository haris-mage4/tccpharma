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
use Amasty\QuoteAttributes\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Psr\Log\LoggerInterface;

class Delete implements DeleteInterface
{
    /**
     * @var AttributeResource
     */
    private $attributeResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        AttributeResource $attributeResource,
        LoggerInterface $logger
    ) {
        $this->attributeResource = $attributeResource;
        $this->logger = $logger;
    }

    /**
     * @param AttributeInterface|Attribute $attribute
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(AttributeInterface $attribute): void
    {
        try {
            $this->attributeResource->delete($attribute);
        } catch (\Exception $e) {
            if ($attribute->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove attribute with ID %1. Error: %2',
                        [$attribute->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove attribute. Error: %1', $e->getMessage()));
        }
    }
}
