<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\ResourceModel\Attribute;

use Amasty\QuoteAttributes\Model\Attribute as AttributeModel;
use Amasty\QuoteAttributes\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EavCollection;

class Collection extends EavCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AttributeModel::class, AttributeResource::class);
    }
}
