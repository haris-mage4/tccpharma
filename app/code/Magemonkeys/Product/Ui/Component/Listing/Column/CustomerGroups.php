<?php
namespace Magemonkeys\Product\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CustomerGroups implements OptionSourceInterface
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(GroupRepositoryInterface $groupRepository,        SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get customer group options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $customerGroups = $this->groupRepository->getList($searchCriteria)->getItems();

        foreach ($customerGroups as $group) {
            $options[] = [
                'value' => $group->getId(),
                'label' => $group->getCode(),
            ];
        }
        return $options;
    }

    
}
