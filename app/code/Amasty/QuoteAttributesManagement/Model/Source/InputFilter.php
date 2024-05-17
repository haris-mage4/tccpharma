<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class InputFilter implements OptionSourceInterface
{
    public const STRIP_TAGS = 'striptags';
    public const ESCAPE_HTML = 'escapehtml';
    public const DATE = 'date';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::STRIP_TAGS,
                'label' => __('Strip HTML Tags')
            ],
            [
                'value' => self::ESCAPE_HTML,
                'label' => __('Escape HTML Entities')
            ],
            [
                'value' => self::DATE,
                'label' => __('Normalize Date')
            ]
        ];
    }

    /**
     * @return array
     */
    public function getOptionsByType(): array
    {
        return [
            FrontendInput::TEXT => [
                [
                    'value' => self::STRIP_TAGS,
                    'label' => __('Strip HTML Tags')
                ],
                [
                    'value' => self::ESCAPE_HTML,
                    'label' => __('Escape HTML Entities')
                ]
            ],
            FrontendInput::TEXTAREA => [
                [
                    'value' => self::STRIP_TAGS,
                    'label' => __('Strip HTML Tags')
                ],
                [
                    'value' => self::ESCAPE_HTML,
                    'label' => __('Escape HTML Entities')
                ]
            ],
            FrontendInput::DATE => [
                [
                    'value' => self::DATE,
                    'label' => __('Normalize Date')
                ]
            ]
        ];
    }
}
