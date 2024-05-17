<?php
namespace Eagle\Sorting\Plugin;

use Magento\CatalogSearch\Controller\Result\Index as SearchResultIndex;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class CustomElasticsearchPlugin
{
    protected $resultRedirectFactory;
    protected $productCollectionFactory;

    public function __construct(
        RedirectFactory $resultRedirectFactory,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function aroundExecute(
        SearchResultIndex $subject,
        \Closure $proceed
    ) {

        $request = $subject->getRequest();
        $query = $request->getParam('q');

        // Modify the query processing if the term is "#trending-asc"
        // if (strpos($query, '#') === 0) {
        //     // Retrieve the product collection
        //     $productCollection = $this->productCollectionFactory->create();

        //     // Modify the collection to search only in product descriptions
        //     $productCollection->addAttributeToSelect('*')
        //         ->addAttributeToFilter('description', ['like' => '%#%'])
        //         ->setPageSize(20); // Adjust the page size as needed
        //         // var_dump( $productCollection );die;
        //     // Set the modified collection in the search result
        //     $subject->getResult()->setProductCollection($productCollection);

        //     // Proceed with the original search execution
        //     $result = $proceed();

        //     return $result;
        // }

        // Proceed with the original search execution
        $result = $proceed();

        return $result;
    }
}
