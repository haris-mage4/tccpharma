<?php
namespace TCCPharma\CatalogUrlRewrite\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductSaveBeforeObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $urlKey = $this->generateUrlKey($product->getSku());
        $product->setUrlKey($urlKey);
    }

    private function generateUrlKey($sku): string
    {
        return strtolower(str_replace([' ', '_', '+'], '-', $sku));
    }
}
