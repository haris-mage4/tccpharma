<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Plugin\RequestQuote\Model\Source\PdfVariables;

use Amasty\RequestQuote\Model\Source\PdfVariables;

class AddAttributesVariable
{
    public const QUOTE_ATTRIBUTES = 'quote_attributes';

    /**
     * @param PdfVariables $subject
     * @param array $options
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(PdfVariables $subject, array $options): array
    {
        $options[] = [
            'value' => self::QUOTE_ATTRIBUTES,
            'label' => __('Quote Attributes')
        ];

        return $options;
    }
}
