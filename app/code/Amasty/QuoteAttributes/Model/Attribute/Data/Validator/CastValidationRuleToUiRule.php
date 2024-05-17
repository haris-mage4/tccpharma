<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Data\Validator;

class CastValidationRuleToUiRule
{
    /**
     * @var array
     */
    private $map = [
        Codes::DECIMAL => 'validate-number',
        Codes::NUMERIC => 'validate-digits',
        Codes::EMAIL => 'validate-email',
        Codes::URL => 'validate-url',
        Codes::ALPHA => 'validate-alpha',
        Codes::ALPHA_NUMERIC => 'validate-alphanum',
        Codes::DATE => 'validate-date'
    ];

    /**
     * Cast given validation rule to UI validation rule.
     *
     * @param string $validationRule
     * @return string
     */
    public function execute(string $validationRule): ?string
    {
        return $this->map[$validationRule] ?? null;
    }
}
