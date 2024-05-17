<?php
namespace Stathmos\Customize\Cron;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryLinkRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class Category
{
	const MAIN_ID = 11;

	const SIX_MONTH = 14;

	const FOUR_MONTH = 15;

	const TWO_MONTH = 16;
    private CollectionFactory $_productCollectionFactory;
    private ProductRepositoryInterface $productRepository;

    private CategoryLinkRepository $categoryLinkRepository;

    private CategoryLinkManagementInterface $categoryLinkManagementInterface;

    public function __construct(
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        CategoryLinkRepository $categoryLinkRepository,
        CategoryLinkManagementInterface $categoryLinkManagementInterface
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->categoryLinkManagement = null;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->categoryLinkManagementInterface = $categoryLinkManagementInterface;
    }

    /**
     * @throws StateException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute()
	{
		$this->resetProduct();
		$this->assignProduct();
	}

    /**
     * @throws NoSuchEntityException
     */
    public function assignProduct(){
		$productCategory = [];
		$startData = date('Y-m-d');
		$twoMonthDate = date('Y-m-d', strtotime('+2 months'));
		$products = $this->getProductCollectionByDate($startData,$twoMonthDate);
		foreach ($products as $product) {
			$sku = $product->getSku();
			$productCategory[$sku][] = self::TWO_MONTH;
		}
		$fourMonthDate = date('Y-m-d', strtotime('+4 months'));
		$products = $this->getProductCollectionByDate($twoMonthDate,$fourMonthDate);
		foreach ($products as $product) {
			$sku = $product->getSku();
			$productCategory[$sku][] = self::FOUR_MONTH;
		}
		$sixMonthDate = date('Y-m-d', strtotime('+6 months'));
		$products = $this->getProductCollectionByDate($fourMonthDate,$sixMonthDate);
		foreach ($products as $product) {
			$sku = $product->getSku();
			$productCategory[$sku][] = self::SIX_MONTH;
		}
		foreach ($productCategory as $key => $value) {
			$value[] = self::MAIN_ID;
			$product = $this->loadProduct($key);
			$categoryIds = $product->getCategoryIds();
			$categoryIds = array_merge($categoryIds,$value);
			$this->getCategoryLinkManagement()->assignProductToCategories($key, $categoryIds);
		}
	}

	public function getProductCollectionByDate($start,$end): Collection
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter(
		    'closest_expiration_date',
		    array(
		        'lteq'=> $end
		    )
		);
		$collection->addFieldToFilter(
		    'closest_expiration_date',
		    array(
		        'gt'=> $start
		    )
		);
        return $collection;
    }

    public function getProductCollectionByCategories($ids): Collection
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => $ids]);
        return $collection;
    }

    /**
     * @throws StateException
     * @throws CouldNotSaveException
     * @throws InputException
     */
    public function resetProduct(){
		$catIds = array('14','15','16');

    	for($i=0; $i<count($catIds); $i++)
    	{
    		$twoMonthDate = '';
    		$collection = $this->getProductCollectionByCategories($catIds[$i]);
			$startData = date('Y-m-d');
			if($catIds[$i] == '14')
			{
				$twoMonthDate = date('Y-m-d', strtotime('+6 months'));
			}
			else if($catIds[$i] == '15')
			{
				$twoMonthDate = date('Y-m-d', strtotime('+4 months'));
			}
			else
			{
				$twoMonthDate = date('Y-m-d', strtotime('+2 months'));
			}

			foreach ($collection as $product) {
				$expdate = $product->getClosestExpirationDate();
				if($expdate)
				{
					if(strtotime($expdate) >= strtotime($startData) && strtotime($expdate) <= strtotime($twoMonthDate))
					{
					} else
					{
						$categoryLinkRepository = $this->categoryLinkRepository;
						$sku = $product->getSku();
						$categoryLinkRepository->deleteByIds(11,$sku);
						$categoryLinkRepository->deleteByIds($catIds[$i],$sku);
					}
				}
			}
    	}
	}

    /**
     * @return CategoryLinkManagementInterface|null
     */
	private function getCategoryLinkManagement(): ?CategoryLinkManagementInterface
    {
	    if (null === $this->categoryLinkManagement) {
	        $this->categoryLinkManagement = $this->categoryLinkManagementInterface;
	    }
	    return $this->categoryLinkManagement;
	}

    /**
     * @throws NoSuchEntityException
     */
    public function loadProduct($sku): ProductInterface
    {
	    return $this->productRepository->get($sku);
	}
}
