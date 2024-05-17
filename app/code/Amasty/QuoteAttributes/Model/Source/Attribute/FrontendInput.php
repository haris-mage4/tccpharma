<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Source\Attribute;

class FrontendInput
{
    public const TEXT = 'text';
    public const TEXTAREA = 'textarea';
    public const DATE = 'date';
    public const SELECT = 'select';
    public const BOOLEAN = 'boolean';
    public const MULTISELECT = 'multiselect';

    /**
     * @return string[]
     */
    public function getAvailableOptions(): array
    {
        return [
            self::TEXT,
            self::TEXTAREA,
            self::DATE,
            self::SELECT,
            self::BOOLEAN,
            self::MULTISELECT
        ];
    }
}
