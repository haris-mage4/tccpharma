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
use Amasty\QuoteAttributes\Model\QuoteEntity\GetEntityTypeId;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity;
use Magento\Eav\Model\Config;

class GetNew implements GetNewInterface
{
    /**
     * @var AttributeInterfaceFactory
     */
    private $attributeFactory;

    /**
     * @var GetEntityTypeId
     */
    private $getEntityTypeId;

    public function __construct(AttributeInterfaceFactory $attributeFactory, GetEntityTypeId $getEntityTypeId)
    {
        $this->attributeFactory = $attributeFactory;
        $this->getEntityTypeId = $getEntityTypeId;
    }

    public function execute(): AttributeInterface
    {
        $attribute = $this->attributeFactory->create();
        $attribute->setEntityTypeId($this->getEntityTypeId->execute());

        return $attribute;
    }
}
