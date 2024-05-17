<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Model\Attribute\Store\Table as StoreTable;
use Amasty\QuoteAttributes\Model\QuoteEntity;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Store\Model\Store;

class GetAttributeList
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        QuoteRepositoryInterface $quoteRepository,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->quoteRepository = $quoteRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param QuoteEntityInterface $quoteEntity
     * @return AttributeInterface[]
     */
    public function execute(QuoteEntityInterface $quoteEntity): array
    {
        $quote = $this->quoteRepository->get($quoteEntity->getQuoteId(), ['*']);

        $this->searchCriteriaBuilder->addFilter(
            StoreTable::STORE_COLUMN,
            [Store::DEFAULT_STORE_ID, (int) $quote->getStoreId()],
            'in'
        );
        $this->searchCriteriaBuilder->setSortOrders([$this->createSortOrder()]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $attributesSearchResult = $this->attributeRepository->getList($searchCriteria);

        return $attributesSearchResult->getItems();
    }

    private function createSortOrder(): AbstractSimpleObject
    {
        $this->sortOrderBuilder->setField(AttributeInterface::SORT_ORDER);
        $this->sortOrderBuilder->setAscendingDirection();

        return $this->sortOrderBuilder->create();
    }
}
