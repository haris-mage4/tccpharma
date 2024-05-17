<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity\Command;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Magento\Framework\Exception\CouldNotSaveException;

interface SaveInterface
{
    /**
     * @param QuoteEntityInterface $quoteEntity
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(QuoteEntityInterface $quoteEntity): void;
}
