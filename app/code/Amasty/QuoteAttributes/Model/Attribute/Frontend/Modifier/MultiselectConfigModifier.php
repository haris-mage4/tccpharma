<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Frontend\Modifier;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute;

class MultiselectConfigModifier implements ModifierInterface
{
    /**
     * @param AttributeInterface|Attribute $attribute
     * @param array $uiConfig
     * @return array
     */
    public function execute(AttributeInterface $attribute, array $uiConfig): array
    {
        $uiConfig['options'] = $attribute->getSource()->getAllOptions();
        return $uiConfig;
    }
}
