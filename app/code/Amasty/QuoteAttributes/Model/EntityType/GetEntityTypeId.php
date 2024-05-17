<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\EntityType;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\LocalizedException;

class GetEntityTypeId
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    public function __construct(EavConfig $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param string $typeCode
     * @return int
     * @throws LocalizedException
     */
    public function execute(string $typeCode): int
    {
        return (int) $this->eavConfig->getEntityType($typeCode)->getEntityTypeId();
    }
}
