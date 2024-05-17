<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Ui\DataProvider\Listing;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\SearchResultFactory;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var array
     */
    private $mappedFields = [
        'default_frontend_label' => AttributeInterface::FRONTEND_LABEL
    ];

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        SearchResultFactory $searchResultFactory,
        AttributeRepositoryInterface $attributeRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->searchResultFactory = $searchResultFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return SearchResultInterface
     */
    public function getSearchResult(): SearchResultInterface
    {
        $searchCriteria = $this->getSearchCriteria();
        $result = $this->attributeRepository->getList($searchCriteria);

        return $this->searchResultFactory->create(
            $result->getItems(),
            $result->getTotalCount(),
            $searchCriteria,
            AttributeInterface::ATTRIBUTE_ID
        );
    }

    /**
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter): void
    {
        if (array_key_exists($filter->getField(), $this->mappedFields)) {
            $filter->setField($this->mappedFields[$filter->getField()]);
        }

        parent::addFilter($filter);
    }

    public function addOrder($field, $direction)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }

        parent::addOrder($field, $direction);
    }
}
