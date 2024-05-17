<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Data\Validator;

class Codes
{
    public const DECIMAL = 'decimal';
    public const NUMERIC = 'numeric';
    public const EMAIL = 'email';
    public const URL = 'url';
    public const ALPHA = 'alpha';
    public const ALPHA_NUMERIC = 'alphanumeric';
    public const DATE = 'date';
}
