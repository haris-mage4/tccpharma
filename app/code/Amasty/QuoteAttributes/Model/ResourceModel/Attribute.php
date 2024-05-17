<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\ResourceModel;

use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttribute;

class Attribute extends EavAttribute
{
    public const TABLE_NAME = 'amasty_quote_attribute_eav_attribute';
}
