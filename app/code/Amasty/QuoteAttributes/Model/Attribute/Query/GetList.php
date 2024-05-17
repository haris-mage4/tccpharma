<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Query;

use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class GetList implements GetListInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $eavAttributeRepository;

    public function __construct(AttributeRepositoryInterface $eavAttributeRepository)
    {
        $this->eavAttributeRepository = $eavAttributeRepository;
    }

    public function execute(SearchCriteriaInterface $searchCriteria): AttributeSearchResultsInterface
    {
        return $this->eavAttributeRepository->getList(QuoteEntity::TYPE_CODE, $searchCriteria);
    }
}
