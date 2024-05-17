<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Frontend;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;

interface CastToUiInterface
{
    /**
     * @param AttributeInterface $attribute
     * @return array
     */
    public function execute(AttributeInterface $attribute): array;
}
