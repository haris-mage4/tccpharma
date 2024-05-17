<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity as QuoteEntityResource;

class FindQuoteEntityId
{
    /**
     * @var QuoteEntityResource
     */
    private $quoteEntityResource;

    public function __construct(QuoteEntityResource $quoteEntityResource)
    {
        $this->quoteEntityResource = $quoteEntityResource;
    }

    /**
     * Find quote entity id from entity table.
     *
     * @param string $fieldName
     * @param string $value
     * @return int
     */
    public function execute(string $fieldName, string $value): int
    {
        $connection = $this->quoteEntityResource->getConnection();
        $select = $connection->select()->from(
            $this->quoteEntityResource->getEntityTable(),
            [QuoteEntityInterface::ENTITY_ID]
        )->where(sprintf('%s = ?', $fieldName), $value);

        return (int) $connection->fetchOne($select);
    }
}
