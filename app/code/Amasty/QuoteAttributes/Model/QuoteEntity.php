<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity as QuoteEntityResource;
use Magento\Framework\Model\AbstractModel;

class QuoteEntity extends AbstractModel implements QuoteEntityInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(QuoteEntityResource::class);
    }

    public function getQuoteId(): ?int
    {
        return $this->hasData(QuoteEntityInterface::QUOTE_ID)
            ? (int) $this->_getData(QuoteEntityInterface::QUOTE_ID)
            : null;
    }

    public function setQuoteId(int $quoteId): void
    {
        $this->setData(QuoteEntityInterface::QUOTE_ID, $quoteId);
    }
}
