<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity\Query;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface GetByQuoteIdInterface
{
    /**
     * @param int $quoteId
     * @return QuoteEntityInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $quoteId): QuoteEntityInterface;
}
