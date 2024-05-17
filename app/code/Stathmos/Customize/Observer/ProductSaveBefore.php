<?php

namespace Stathmos\Customize\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;

class ProductSaveBefore implements ObserverInterface
{
    protected Product $product;
    protected ProductRepository $productRepository;
    private StoreManagerInterface $_storeManager;
    private ResourceConnection $_resource;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param Product $product
     * @param ProductRepository $productRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        Product $product,
        ProductRepository $productRepository
    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->product = $product;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $sku = $product->getSku();
        $productExists = $this->product->getIdBySku($sku);
        if (!$productExists) {
            $url = preg_replace('#[^0-9a-z]+#i', '-', $product->getName());
            $nameUrlKey = strtolower($url);
            $urlKey = $nameUrlKey.'.html';
            $connection = $this->_resource->getConnection();
            $query = 'SELECT * FROM `url_rewrite` WHERE `request_path` LIKE "' . $urlKey . '"';
            $result = $connection->fetchAll($query);

            if (count($result) > 0) {
                $url = preg_replace('#[^0-9a-z]+#i', '-', $sku);
                $skusmall = strtolower($url);
                $purlkey = $nameUrlKey;
                $new = $purlkey .'-'. $skusmall;
                $newurl = $product->setUrlKey($new);
            }
        }
    }
}
