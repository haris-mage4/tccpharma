<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Query;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterfaceFactory;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Attribute\Extensions\Stores\ReadHandler as StoresReadHandler;
use Amasty\QuoteAttributes\Model\QuoteEntity\GetEntityTypeId;
use Amasty\QuoteAttributes\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Framework\Exception\NoSuchEntityException;

class GetById implements GetByIdInterface
{
    /**
     * @var AttributeInterfaceFactory
     */
    private $attributeFactory;

    /**
     * @var AttributeResource
     */
    private $attributeResource;

    /**
     * @var GetEntityTypeId
     */
    private $getEntityTypeId;
    /**
     * @var StoresReadHandler
     */
    private $storesReadHandler;

    public function __construct(
        AttributeInterfaceFactory $attributeFactory,
        AttributeResource $attributeResource,
        GetEntityTypeId $getEntityTypeId,
        StoresReadHandler $storesReadHandler
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeResource = $attributeResource;
        $this->getEntityTypeId = $getEntityTypeId;
        $this->storesReadHandler = $storesReadHandler;
    }

    public function execute(int $id): AttributeInterface
    {
        /** @var AttributeInterface|Attribute $attribute */
        $attribute = $this->attributeFactory->create();
        $this->attributeResource->load($attribute, $id);
        $this->storesReadHandler->execute($attribute);

        if ($attribute->getId() === null || $attribute->getEntityTypeId() != $this->getEntityTypeId->execute()) {
            throw new NoSuchEntityException(
                __('Attribute with id "%value" does not exist.', ['value' => $id])
            );
        }

        return $attribute;
    }
}
