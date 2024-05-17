<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Frontend\Modifier;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Magento\Config\Model\Config\Source\Yesno as YesnoSource;

class BooleanConfigModifier implements ModifierInterface
{
    /**
     * @var YesnoSource
     */
    private $yesnoSoruce;

    public function __construct(YesnoSource $yesnoSoruce)
    {
        $this->yesnoSoruce = $yesnoSoruce;
    }

    public function execute(AttributeInterface $attribute, array $uiConfig): array
    {
        $uiConfig['options'] = $this->yesnoSoruce->toOptionArray();
        return $uiConfig;
    }
}
