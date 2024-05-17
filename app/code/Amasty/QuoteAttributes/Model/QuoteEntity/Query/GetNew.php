<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity\Query;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterfaceFactory;

class GetNew implements GetNewInterface
{
    /**
     * @var QuoteEntityInterfaceFactory
     */
    private $quoteEntityFactory;

    public function __construct(QuoteEntityInterfaceFactory $quoteEntityFactory)
    {
        $this->quoteEntityFactory = $quoteEntityFactory;
    }

    public function execute(): QuoteEntityInterface
    {
        return $this->quoteEntityFactory->create();
    }
}
