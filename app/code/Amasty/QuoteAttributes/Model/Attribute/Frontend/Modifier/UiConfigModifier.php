<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Frontend\Modifier;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;

class UiConfigModifier implements ModifierInterface
{
    /**
     * @var ModifierInterface[]
     */
    private $uiConfigModifiers;

    public function __construct(array $uiConfigModifiers = [])
    {
        $this->uiConfigModifiers = $uiConfigModifiers;
    }

    public function execute(AttributeInterface $attribute, array $uiConfig): array
    {
        $uiConfigModifier = $this->uiConfigModifiers[$attribute->getFrontendInput()] ?? null;
        if ($uiConfigModifier) {
            $uiConfig = $uiConfigModifier->execute($attribute, $uiConfig);
        }

        return $uiConfig;
    }
}
