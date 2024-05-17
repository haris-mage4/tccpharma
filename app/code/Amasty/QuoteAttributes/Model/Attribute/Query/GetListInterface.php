<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Query;

use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface GetListInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return AttributeSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): AttributeSearchResultsInterface;
}
