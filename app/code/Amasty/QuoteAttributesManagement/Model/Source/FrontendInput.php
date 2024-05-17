<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Model\Source;

use Amasty\QuoteAttributes\Model\Source\Attribute\FrontendInput as FrontendInputAvailable;
use Magento\Framework\Data\OptionSourceInterface;

class FrontendInput extends FrontendInputAvailable implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::TEXT,
                'label' => __('Text Field')
            ],
            [
                'value' => self::TEXTAREA,
                'label' => __('Text Area')
            ],
            [
                'value' => self::DATE,
                'label' => __('Date')
            ],
            [
                'value' => self::SELECT,
                'label' => __('Dropdown')
            ],
            [
                'value' => self::BOOLEAN,
                'label' => __('Yes/No')
            ],
            [
                'value' => self::MULTISELECT,
                'label' => __('Multiple Select')
            ]
        ];
    }
}
