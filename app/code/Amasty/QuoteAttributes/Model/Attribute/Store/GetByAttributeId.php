<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Store;

use Amasty\QuoteAttributes\Model\ResourceModel\Attribute\Store\LoadByAttributeId;

class GetByAttributeId
{
    /**
     * @var LoadByAttributeId
     */
    private $loadByAttributeId;

    public function __construct(LoadByAttributeId $loadByAttributeId)
    {
        $this->loadByAttributeId = $loadByAttributeId;
    }

    public function execute(int $attributeId): array
    {
        return $this->loadByAttributeId->execute($attributeId);
    }
}
