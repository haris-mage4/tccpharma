<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Data;

use Amasty\QuoteAttributes\Model\Attribute\Data\Validator\Codes;
use Amasty\QuoteAttributes\Model\Attribute\Data\Validator\DecimalValidator;
use Magento\Eav\Model\Attribute\Data\Text as EavText;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Psr\Log\LoggerInterface;

class Text extends EavText
{
    /**
     * @var DecimalValidator
     */
    private $decimalValidator;

    public function __construct(
        TimezoneInterface $localeDate,
        LoggerInterface $logger,
        ResolverInterface $localeResolver,
        StringUtils $stringHelper,
        DecimalValidator $decimalValidator
    ) {
        parent::__construct($localeDate, $logger, $localeResolver, $stringHelper);
        $this->decimalValidator = $decimalValidator;
    }

    /**
     * @param string $value
     * @return array|bool|true
     * @throws LocalizedException
     */
    protected function _validateInputRule($value)
    {
        $validateRules = $this->getAttribute()->getValidateRules();

        if (!empty($validateRules['input_validation'])) {
            if ($validateRules['input_validation'] === Codes::DECIMAL) {
                return $this->decimalValidator->validate($this->getAttribute(), $value);
            } else {
                return parent::_validateInputRule($value);
            }
        }

        return true;
    }
}
