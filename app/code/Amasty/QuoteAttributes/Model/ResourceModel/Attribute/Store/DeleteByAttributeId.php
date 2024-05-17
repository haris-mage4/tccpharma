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

class DeleteByAttributeId
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
     * @return void
     */
    public function execute(int $attributeId): void
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName(StoreTable::NAME),
            [sprintf('%s = ?', StoreTable::ATTRIBUTE_COLUMN) => $attributeId]
        );
    }
}
