<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity;

use Amasty\QuoteAttributes\Model\EntityType\GetEntityTypeId as GetEntityTypeIdByCode;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity;
use Magento\Framework\Exception\LocalizedException;

class GetEntityTypeId
{
    /**
     * @var int|null
     */
    private $entityTypeId;

    /**
     * @var GetEntityTypeIdByCode
     */
    private $getEntityTypeId;

    public function __construct(GetEntityTypeIdByCode $getEntityTypeId)
    {
        $this->getEntityTypeId = $getEntityTypeId;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function execute(): int
    {
        if ($this->entityTypeId === null) {
            $this->entityTypeId = $this->getEntityTypeId->execute(QuoteEntity::TYPE_CODE);
        }

        return $this->entityTypeId;
    }
}
