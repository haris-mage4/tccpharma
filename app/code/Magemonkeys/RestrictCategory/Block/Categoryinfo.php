<?php
namespace Magemonkeys\RestrictCategory\Block;

class Categoryinfo extends \Magento\Framework\View\Element\Template
{    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $data);
    }

    /* $categoryId as category id */
    public function getCategoryById($categoryId){
        try {
            return $category = $this->categoryRepository->get($categoryId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return ['response' => 'Category Not Found'];
        }
    }

}
?>