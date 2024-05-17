<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\SearchCriteria\CollectionProcessor\FilterProcessor;

use Amasty\QuoteAttributes\Model\ResourceModel\Attribute\Store\JoinToCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class AttributeStoreFilter implements CustomFilterInterface
{
    /**
     * @var JoinToCollection
     */
    private $joinToCollection;

    public function __construct(JoinToCollection $joinToCollection)
    {
        $this->joinToCollection = $joinToCollection;
    }

    /**
     * Use custom filter for join store table,
     * return false mean that original processor must apply filter.
     *
     * @param Filter $filter
     * @param AbstractDb|Collection $collection
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Filter $filter, AbstractDb $collection): bool
    {
        $this->joinToCollection->execute($collection);
        return false;
    }
}
