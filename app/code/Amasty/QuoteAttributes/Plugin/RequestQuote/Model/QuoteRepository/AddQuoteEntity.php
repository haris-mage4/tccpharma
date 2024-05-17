<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Plugin\RequestQuote\Model\QuoteRepository;

use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\QuoteRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class AddQuoteEntity
{
    /**
     * @var QuoteEntityRepositoryInterface
     */
    private $quoteEntityRepository;

    public function __construct(QuoteEntityRepositoryInterface $quoteEntityRepository)
    {
        $this->quoteEntityRepository = $quoteEntityRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param QuoteRepository $subject
     * @param QuoteInterface $quote
     * @return QuoteInterface
     *
     * @see QuoteRepository::get
     */
    public function afterGet(QuoteRepository $subject, QuoteInterface $quote): QuoteInterface
    {
        if ($quote->getExtensionAttributes()->getQuoteEntity() === null) {
            try {
                $quoteEntity = $this->quoteEntityRepository->getByQuoteId((int)$quote->getId());
            } catch (NoSuchEntityException $e) {
                $quoteEntity = $this->quoteEntityRepository->getNew();
                $quoteEntity->setQuoteId((int) $quote->getId());
            }
            $quote->getExtensionAttributes()->setQuoteEntity($quoteEntity);
        }

        return $quote;
    }
}
