<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity\Query;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;

interface GetNewInterface
{
    /**
     * @return QuoteEntityInterface
     */
    public function execute(): QuoteEntityInterface;
}
