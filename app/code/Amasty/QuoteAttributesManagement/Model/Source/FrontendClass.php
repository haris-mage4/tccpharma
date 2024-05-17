<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Model\Source;

use Magento\Eav\Model\Adminhtml\Attribute\Validation\Rules\Options as TextOptions;

class FrontendClass
{
    /**
     * @var TextOptions
     */
    private $textOptions;

    public function __construct(TextOptions $textOptions)
    {
        $this->textOptions = $textOptions;
    }

    /**
     * @return array
     */
    public function getOptionsByType(): array
    {
        return [
            FrontendInput::TEXT => $this->textOptions->toOptionArray(),
            FrontendInput::DATE => [
                ['value' => '', 'label' => __('None')],
                ['value' => 'validate-date', 'label' => __('Date')]
            ]
        ];
    }
}
