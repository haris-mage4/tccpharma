<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete\VariablesValue\Retrievers;

use Magento\Customer\Model\Attribute as AttributeModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DateRetriever implements RetrieverInterface
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    public function retrieve(AttributeModel $attribute, string $value): string
    {
        return $this->timezone->formatDateTime(
            $value,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE
        );
    }
}
