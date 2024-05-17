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
use Zend_Db_Exception;

class InsertMultiple
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
     * @param array $data
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(array $data): void
    {
        $this->resourceConnection->getConnection()->insertMultiple(
            $this->resourceConnection->getTableName(StoreTable::NAME),
            $data
        );
    }
}
