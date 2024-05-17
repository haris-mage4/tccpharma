<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Store;

use Amasty\QuoteAttributes\Model\ResourceModel\Attribute\Store\DeleteByAttributeId;
use Amasty\QuoteAttributes\Model\ResourceModel\Attribute\Store\InsertMultiple;
use Zend_Db_Exception;

class SaveMultiple
{
    /**
     * @var InsertMultiple
     */
    private $insertMultiple;

    /**
     * @var DeleteByAttributeId
     */
    private $deleteByAttributeId;

    public function __construct(
        InsertMultiple $insertMultiple,
        DeleteByAttributeId $deleteByAttributeId
    ) {
        $this->insertMultiple = $insertMultiple;
        $this->deleteByAttributeId = $deleteByAttributeId;
    }

    /**
     * @param int $attributeId
     * @param array $stores
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(int $attributeId, array $stores): void
    {
        $data = [];
        foreach ($stores as $storeId) {
            $data[] = [
                Table::ATTRIBUTE_COLUMN => $attributeId,
                Table::STORE_COLUMN => (int) $storeId
            ];
        }
        $this->deleteByAttributeId->execute($attributeId);
        $this->insertMultiple->execute($data);
    }
}
