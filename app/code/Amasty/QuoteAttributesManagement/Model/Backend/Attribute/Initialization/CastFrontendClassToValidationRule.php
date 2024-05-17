<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Model\Backend\Attribute\Initialization;

use Amasty\QuoteAttributes\Model\Attribute\Data\Validator\Codes;

class CastFrontendClassToValidationRule
{
    /**
     * @var array
     */
    private $map = [
        'validate-number' => Codes::DECIMAL,
        'validate-digits' => Codes::NUMERIC,
        'validate-email' => Codes::EMAIL,
        'validate-url' => Codes::URL,
        'validate-alpha' => Codes::ALPHA,
        'validate-alphanum' => Codes::ALPHA_NUMERIC,
        'validate-date' => Codes::DATE
    ];

    /**
     * Cast given frontend class to validation rule.
     * Validation rule used for validate in data model of attribute.
     * @see \Magento\Eav\Model\Attribute\Data\AbstractData::_validateInputRule
     *
     * @param string $frontendClass
     * @return string
     */
    public function execute(string $frontendClass): ?string
    {
        return $this->map[$frontendClass] ?? null;
    }
}
