<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Ui\DataProvider\Form;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributesManagement\Controller\Adminhtml\Attribute\Save;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\SearchResultFactory;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var array|null
     */
    private $loadedData;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

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
        PoolInterface $pool,
        DataPersistorInterface $dataPersistor,
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
        $this->pool = $pool;
        $this->attributeRepository = $attributeRepository;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if ($this->loadedData === null) {
            $attributeData = $this->dataPersistor->get(Save::PERSISTENT_NAME);
            if ($attributeData) {
                $this->dataPersistor->clear(Save::PERSISTENT_NAME);
                $attributeId = $attributeData[AttributeInterface::ATTRIBUTE_ID] ?? null;
                $this->loadedData[$attributeId] = $attributeData;
            } else {
                $this->loadedData = [];
                $data = parent::getData();
                foreach ($data['items'] as $attributeData) {
                    foreach ($this->pool->getModifiersInstances() as $modifier) {
                        $attributeData = $modifier->modifyData($attributeData);
                        $this->loadedData[$attributeData[AttributeInterface::ATTRIBUTE_ID]] = $attributeData;
                    }
                }
            }
        }

        return $this->loadedData;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * @return SearchResultInterface
     */
    public function getSearchResult(): SearchResultInterface
    {
        $searchResult = $this->attributeRepository->getList($this->getSearchCriteria());
        return $this->searchResultFactory->create(
            $searchResult->getItems(),
            $searchResult->getTotalCount(),
            $this->getSearchCriteria(),
            AttributeInterface::ATTRIBUTE_ID
        );
    }
}
