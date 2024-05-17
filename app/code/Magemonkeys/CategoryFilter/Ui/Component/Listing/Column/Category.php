<?php

namespace Magemonkeys\CategoryFilter\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Category extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Magento\Catalog\Model\ProductCategoryList
     */
    private $productCategory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param ContextInterface                                 $context
     * @param UiComponentFactory                               $uiComponentFactory
     * @param array                                            $components
     * @param array                                            $data
     * @param \Magento\Catalog\Model\ProductCategoryList       $productCategory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \Magento\Catalog\Model\ProductCategoryList $productCategory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productCategory = $productCategory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Prepare date for category column
     * @param  array  $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getData('name');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $productId = $item['entity_id'];
                $categoryIds = $this->getCategoryIds($productId);
                $categories = [];
                if (count($categoryIds)) {
                    foreach ($categoryIds as $categoryId) {
                        $categoryData = $this->categoryRepository->get($categoryId);
                        $categories[] = $categoryData->getName();
                    }
                }
                $item[$fieldName] = implode(',', $categories);
            }
        }
        return $dataSource;
    }


    /**
     * get all the category id
     *
     * @param int $productId
     * @return array
     */
    private function getCategoryIds(int $productId)
    {
        $categoryIds = $this->productCategory->getCategoryIds($productId);
        $category = [];
        if ($categoryIds) {
            $category = array_unique($categoryIds);
        }
        return $category;
    }
}