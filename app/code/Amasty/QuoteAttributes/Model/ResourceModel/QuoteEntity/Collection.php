<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity;

use Amasty\QuoteAttributes\Model\QuoteEntity as QuoteEntityModel;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity as QuoteEntityResource;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QuoteEntityModel::class, QuoteEntityResource::class);
    }
}
