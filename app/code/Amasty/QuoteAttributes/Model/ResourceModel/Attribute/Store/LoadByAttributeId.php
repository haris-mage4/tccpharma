<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\ResourceModel\Attribute\Store;

use Amasty\QuoteAttributes\Model\Attribute\Store\Table as StoreTable;
use Magento\Framework\App\ResourceConnection;

class LoadByAttributeId
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
     * @param int $attributeId
     * @return array
     */
    public function execute(int $attributeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $this->resourceConnection->getTableName(StoreTable::NAME),
            [StoreTable::STORE_COLUMN]
        )->where(sprintf('%s = ?', StoreTable::ATTRIBUTE_COLUMN), $attributeId);

        return $connection->fetchCol($select);
    }
}
