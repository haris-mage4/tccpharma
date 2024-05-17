<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Api\Data;

use Magento\Eav\Api\Data\AttributeInterface as EavAttributeInterface;

interface AttributeInterface extends EavAttributeInterface
{
    public const INPUT_FILTER = 'input_filter';
    public const SORT_ORDER = 'sort_order';
    public const IS_USED_IN_GRID = 'used_in_grid';
    public const IS_VISIBLE_IN_GRID = 'is_visible_in_grid';
    public const IS_FILTERABLE_IN_GRID = 'filterable_in_grid';
    public const IS_SEARCHABLE_IN_GRID = 'is_searchable_in_grid';
    public const IS_INCLUDE_IN_PDF = 'is_include_in_pdf';
    public const IS_INCLUDE_IN_EMAIL = 'is_include_in_email';
    public const DEFAULT_VALUE = 'default_value';
    public const DATA_MODEL = 'data_model';

    /**
     * @return string|null
     */
    public function getInputFilter(): ?string;

    /**
     * @param string|null $inputFilter
     * @return void
     */
    public function setInputFilter(?string $inputFilter): void;

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int;

    /**
     * @param int $sortOrder
     * @return void
     */
    public function setSortOrder(int $sortOrder): void;

    /**
     * @return bool
     */
    public function isUsedInGrid(): bool;

    /**
     * @param bool $isUsedInGrid
     * @return void
     */
    public function setIsUsedInGrid(bool $isUsedInGrid): void;

    /**
     * @return bool
     */
    public function isFilterableInGrid(): bool;

    /**
     * @param bool $isFilterableInGrid
     * @return void
     */
    public function setIsFilterableInGrid(bool $isFilterableInGrid): void;

    /**
     * @return bool
     */
    public function isVisibleInGrid(): bool;

    /**
     * @param bool $isVisibleInGrid
     * @return void
     */
    public function setIsVisibleInGrid(bool $isVisibleInGrid): void;

    /**
     * @param bool $isSearchableInGrid
     * @return void
     */
    public function setIsSearchableInGrid(bool $isSearchableInGrid): void;

    /**
     * @return bool
     */
    public function isSearchableInGrid(): bool;

    /**
     * @param bool $isIncludeInPdf
     * @return void
     */
    public function setIsIncludeInPdf(bool $isIncludeInPdf): void;

    /**
     * @return bool
     */
    public function isIncludeInPdf(): bool;

    /**
     * @param bool $isIncludeInEmail
     * @return void
     */
    public function setIsIncludeInEmail(bool $isIncludeInEmail): void;

    /**
     * @return bool
     */
    public function isIncludeInEmail(): bool;
}
