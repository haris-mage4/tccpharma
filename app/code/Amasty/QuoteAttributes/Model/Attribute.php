<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Magento\Eav\Model\Attribute as EavAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\Table as SourceTable;

class Attribute extends EavAttribute implements AttributeInterface
{
    public const MODULE_NAME = 'Amasty_QuoteAttributes';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Customer\Model\ResourceModel\Attribute::class);
    }

    public function getInputFilter(): ?string
    {
        return $this->hasData(AttributeInterface::INPUT_FILTER)
            ? (string) $this->_getData(AttributeInterface::INPUT_FILTER)
            : null;
    }

    public function setInputFilter(?string $inputFilter): void
    {
        $this->setData(AttributeInterface::INPUT_FILTER, $inputFilter);
    }

    public function getSortOrder(): ?int
    {
        return $this->hasData(AttributeInterface::SORT_ORDER)
            ? (int) $this->_getData(AttributeInterface::SORT_ORDER)
            : null;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->setData(AttributeInterface::SORT_ORDER, $sortOrder);
    }

    public function isUsedInGrid(): bool
    {
        return (bool) $this->_getData(AttributeInterface::IS_USED_IN_GRID);
    }

    public function setIsUsedInGrid(bool $isUsedInGrid): void
    {
        $this->setData(AttributeInterface::IS_USED_IN_GRID, $isUsedInGrid);
    }

    public function isFilterableInGrid(): bool
    {
        return (bool) $this->_getData(AttributeInterface::IS_FILTERABLE_IN_GRID);
    }

    public function setIsFilterableInGrid(bool $isFilterableInGrid): void
    {
        $this->setData(AttributeInterface::IS_FILTERABLE_IN_GRID, $isFilterableInGrid);
    }

    public function isVisibleInGrid(): bool
    {
        return (bool) $this->_getData(AttributeInterface::IS_VISIBLE_IN_GRID);
    }

    public function setIsVisibleInGrid(bool $isVisibleInGrid): void
    {
        $this->setData(AttributeInterface::IS_VISIBLE_IN_GRID, $isVisibleInGrid);
    }

    public function setIsSearchableInGrid(bool $isSearchableInGrid): void
    {
        $this->setData(AttributeInterface::IS_SEARCHABLE_IN_GRID, $isSearchableInGrid);
    }

    public function isSearchableInGrid(): bool
    {
        return (bool) $this->_getData(AttributeInterface::IS_SEARCHABLE_IN_GRID);
    }

    public function setIsIncludeInPdf(bool $isIncludeInPdf): void
    {
        $this->setData(AttributeInterface::IS_INCLUDE_IN_PDF, $isIncludeInPdf);
    }

    public function isIncludeInPdf(): bool
    {
        return (bool) $this->_getData(AttributeInterface::IS_INCLUDE_IN_PDF);
    }

    public function setIsIncludeInEmail(bool $isIncludeInEmail): void
    {
        $this->setData(AttributeInterface::IS_INCLUDE_IN_EMAIL, $isIncludeInEmail);
    }

    public function isIncludeInEmail(): bool
    {
        return (bool) $this->_getData(AttributeInterface::IS_INCLUDE_IN_EMAIL);
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function _getDefaultSourceModel(): string
    {
        return SourceTable::class;
    }
}
