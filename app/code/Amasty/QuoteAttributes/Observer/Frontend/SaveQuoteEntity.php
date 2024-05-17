<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Observer\Frontend;

use Amasty\QuoteAttributes\Block\QuoteCart\AttributesProcessor;
use Amasty\QuoteAttributes\Model\Request\QuoteEntity\UpdateData;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Validator\Exception as ValidatorException;

class SaveQuoteEntity implements ObserverInterface
{
    /**
     * @var UpdateData
     */
    private $updateData;

    public function __construct(UpdateData $updateData)
    {
        $this->updateData = $updateData;
    }

    /**
     * event: amasty_request_quote_submit_before
     *
     * @param Observer $observer
     * @return void
     * @throws ValidatorException
     */
    public function execute(Observer $observer): void
    {
        /** @var QuoteInterface $quote */
        $quote = $observer->getData('quote');
        if ($quote && $quote->getExtensionAttributes()->getQuoteEntity()) {
            $this->updateData->execute(
                $quote->getExtensionAttributes()->getQuoteEntity(),
                AttributesProcessor::QUOTE_ENTITY_SCOPE,
                false
            );
        }
    }
}
