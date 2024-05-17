<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Ui\Component\Quote\Listing;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class AttributeProvider
{
    /**
     * @var AttributeInterface[]|null
     */
    private $attributes;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Provide attributes which used on quote grid.
     *
     * @return AttributeInterface[]
     */
    public function execute(): array
    {
        if ($this->attributes === null) {
            $this->searchCriteriaBuilder->addFilter(AttributeInterface::IS_USED_IN_GRID, true);
            $this->attributes = $this->attributeRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        }

        return $this->attributes;
    }
}
