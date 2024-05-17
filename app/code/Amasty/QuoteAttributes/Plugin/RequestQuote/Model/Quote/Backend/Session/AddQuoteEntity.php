<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Plugin\RequestQuote\Model\Quote\Backend\Session;

use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;
use Amasty\RequestQuote\Model\Quote\Backend\Session as BackendSession;
use Amasty\RequestQuote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;

class AddQuoteEntity
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteEntityRepositoryInterface
     */
    private $quoteEntityRepository;

    public function __construct(QuoteResource $quoteResource, QuoteEntityRepositoryInterface $quoteEntityRepository)
    {
        $this->quoteResource = $quoteResource;
        $this->quoteEntityRepository = $quoteEntityRepository;
    }

    /**
     * If quote loaded as magento quote,
     * check is amasty quote and load quote entity.
     *
     * @param BackendSession $backendSession
     * @param CartInterface $quote
     * @return CartInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuote(BackendSession $backendSession, CartInterface $quote): CartInterface
    {
        $quoteId = (int) $quote->getId();
        if ($quote->getExtensionAttributes()->getQuoteEntity() === null
            && $this->quoteResource->isAmastyQuote($quoteId)
        ) {
            try {
                $quoteEntity = $this->quoteEntityRepository->getByQuoteId($quoteId);
            } catch (NoSuchEntityException $e) {
                $quoteEntity = $this->quoteEntityRepository->getNew();
                $quoteEntity->setQuoteId($quoteId);
            }
            $quote->getExtensionAttributes()->setQuoteEntity($quoteEntity);
        }

        return $quote;
    }
}
