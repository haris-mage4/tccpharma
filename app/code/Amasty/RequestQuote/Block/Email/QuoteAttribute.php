<?php

declare(strict_types=1);

namespace Amasty\RequestQuote\Block\Email;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\ViewModel\QuoteLoader;
use Magento\Sales\Block\Items\AbstractItems;

class QuoteAttribute extends AbstractItems
{

    public function getQuote(): ?QuoteInterface
    {
        $quoteId = $this->getData('quote')->getId();

        /** @var QuoteLoader $quoteLoader */
        $quoteLoader = $this->getData('quote_loader');

        if ($quoteId && $quoteLoader) {
            return $quoteLoader->load((int) $quoteId);
        }

        return $quoteId;
    }

    public function getId()
    {
        return $this->getData('quote')->getId();
    }
}
