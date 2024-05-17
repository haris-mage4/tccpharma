<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Model\Attribute;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as OptionCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Store\Api\StoreRepositoryInterface;

class GetOptions
{
    private const OPTION_BATCH_SIZE = 200;

    /**
     * @var OptionCollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var array
     */
    private $valuesByStore = [];

    /**
     * @var array
     */
    private $options = [];

    public function __construct(
        OptionCollectionFactory $optionCollectionFactory,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param AttributeInterface $attribute
     * @return array
     */
    public function execute(AttributeInterface $attribute): array
    {
        if (!isset($this->options[$attribute->getAttributeId()])) {
            $optionCollection = $this->optionCollectionFactory->create()->setAttributeFilter(
                $attribute->getAttributeId()
            )->setPositionOrder(
                OptionCollection::SORT_ORDER_ASC,
                true
            );

            $values = [];
            $optionCollection->setPageSize(self::OPTION_BATCH_SIZE);
            $pageCount = $optionCollection->getLastPageNumber();
            $currentPage = 1;
            while ($currentPage <= $pageCount) {
                $optionCollection->clear();
                $optionCollection->setCurPage($currentPage);
                $values[] = $this->getPreparedValues($attribute, $optionCollection);
                $currentPage++;
            }

            $this->options[$attribute->getAttributeId()] = array_merge([], ...$values);
        }

        return $this->options[$attribute->getAttributeId()];
    }

    private function getPreparedValues(AttributeInterface $attribute, OptionCollection $optionCollection): array
    {
        $values = [];
        $defaultValues = explode(',', (string) $attribute->getDefaultValue());
        foreach ($optionCollection as $option) {
            $optionId = $option->getId();

            $value['default'] = in_array($optionId, $defaultValues);
            $value['id'] = $optionId;
            $value['sort_order'] = $option->getSortOrder();

            $labels = [];
            foreach ($this->storeRepository->getList() as $store) {
                $storeId = (int) $store->getId();
                $storeValues = $this->getStoreOptionValues((int) $attribute->getAttributeId(), $storeId);
                $labels[sprintf('value_option_%d', $storeId)] = $storeValues[$optionId] ?? '';
            }
            $value['labels'] = $labels;

            $values[] = $value;
        }

        return $values;
    }

    private function getStoreOptionValues(int $attributeId, int $storeId): array
    {
        if (!isset($this->valuesByStore[$attributeId][$storeId])) {
            $values = [];
            $valuesCollection = $this->optionCollectionFactory->create()->setAttributeFilter(
                $attributeId
            )->setStoreFilter(
                $storeId,
                false
            );
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
            }
            $this->valuesByStore[$attributeId][$storeId] = $values;
        }

        return $this->valuesByStore[$attributeId][$storeId];
    }
}
