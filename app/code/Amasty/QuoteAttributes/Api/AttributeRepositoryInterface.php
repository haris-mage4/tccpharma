<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Api;

interface AttributeRepositoryInterface
{
    /**
     * @return \Amasty\QuoteAttributes\Api\Data\AttributeInterface
     */
    public function getNew(): \Amasty\QuoteAttributes\Api\Data\AttributeInterface;

    /**
     * @param string $attributeCode
     * @return \Amasty\QuoteAttributes\Api\Data\AttributeInterface
     */
    public function get(string $attributeCode): \Amasty\QuoteAttributes\Api\Data\AttributeInterface;

    /**
     * @param int $id
     * @return \Amasty\QuoteAttributes\Api\Data\AttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\QuoteAttributes\Api\Data\AttributeInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Magento\Eav\Api\Data\AttributeSearchResultsInterface;

    /**
     * @param \Amasty\QuoteAttributes\Api\Data\AttributeInterface $attribute
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\QuoteAttributes\Api\Data\AttributeInterface $attribute): void;

    /**
     * @param \Amasty\QuoteAttributes\Api\Data\AttributeInterface $attribute
     * @return void
     */
    public function delete(\Amasty\QuoteAttributes\Api\Data\AttributeInterface $attribute): void;

    /**
     * @param int $id
     * @return void
     */
    public function deleteById(int $id): void;
}
