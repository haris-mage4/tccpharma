<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Extensions\Stores;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute\Store\GetByAttributeId as GetStoresByAttributeId;

class ReadHandler
{
    /**
     * @var GetStoresByAttributeId
     */
    private $getStoresByAttributeId;

    public function __construct(GetStoresByAttributeId $getStoresByAttributeId)
    {
        $this->getStoresByAttributeId = $getStoresByAttributeId;
    }

    /**
     * @param AttributeInterface $attribute
     * @return void
     */
    public function execute(AttributeInterface $attribute): void
    {
        if ($attribute->getExtensionAttributes()->getAmastyStores() === null) {
            $attribute->getExtensionAttributes()->setAmastyStores(
                $this->getStoresByAttributeId->execute((int) $attribute->getAttributeId())
            );
        }
    }
}
