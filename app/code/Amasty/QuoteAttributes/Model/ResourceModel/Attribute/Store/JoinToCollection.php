<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\ResourceModel\Attribute\Store;

use Amasty\QuoteAttributes\Model\Attribute\Store\Table as StoreTable;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;

class JoinToCollection
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param AbstractDb|Collection $collection
     * @return void
     */
    public function execute(AbstractDb $collection): void
    {
        $collection->join(
            ['store' => $this->resourceConnection->getTableName(StoreTable::NAME)],
            sprintf('main_table.attribute_id = store.%s', StoreTable::ATTRIBUTE_COLUMN),
            [StoreTable::STORE_COLUMN]
        );
    }
}
