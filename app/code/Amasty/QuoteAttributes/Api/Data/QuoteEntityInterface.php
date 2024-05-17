<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Api\Data;

interface QuoteEntityInterface
{
    public const ENTITY_ID = 'entity_id';
    public const QUOTE_ID = 'quote_id';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return \Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getQuoteId(): ?int;

    /**
     * @param int $quoteId
     * @return void
     */
    public function setQuoteId(int $quoteId): void;
}
