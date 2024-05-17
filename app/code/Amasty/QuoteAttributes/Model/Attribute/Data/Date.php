<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Data;

use Magento\Eav\Model\Attribute\Data\Date as EavDate;

class Date extends EavDate
{
    public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        if ($value) {
            $this->_dateFilterFormat(\IntlDateFormatter::SHORT);
            $value = $this->_applyOutputFilter($value);
        }

        return $value;
    }
}
