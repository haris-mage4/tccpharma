<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Api;

use Magento\Framework\Exception\CouldNotSaveException;

interface QuoteEntityRepositoryInterface
{
    /**
     * @param int $id
     * @return \Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface
     */
    public function get(int $id): \Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;

    /**
     * @param int $quoteId
     * @return \Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface
     */
    public function getByQuoteId(int $quoteId): \Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;

    /**
     * @return \Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface
     */
    public function getNew(): \Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;

    /**
     * @param \Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface $quoteEntity
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(\Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface $quoteEntity): void;
}
