<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Source;

use Magento\Config\Model\Config\Source\Yesno as YesNoSource;
use Magento\Framework\Data\OptionSourceInterface;

class YesNoSystemConfig extends YesNoSource implements OptionSourceInterface
{
    const SYSTEM_CONFIG_VALUE = 2;

    public function toOptionArray(): array
    {
        $values = parent::toOptionArray();
        array_unshift(
            $values,
            ['label' => __('Use System Config Value'), 'value' => self::SYSTEM_CONFIG_VALUE]
        );

        return $values;
    }
}
