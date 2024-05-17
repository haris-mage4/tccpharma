<?php

namespace Eagle\Sorting\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Block\Product\ProductList\Toolbar as CoreToolbar;

class StockLast implements ObserverInterface
{
    protected  $scopeConfig;
    protected  $_storeManager;
    protected  $coreToolbar;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CoreToolbar $toolbar
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CoreToolbar $toolbar
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->coreToolbar = $toolbar;
    }
    public function execute(Observer $observer)
    {
        $collection = $observer->getEvent()->getData('collection');
        try {

            if ($this->coreToolbar->getCurrentOrder() == 'stock') {
                $websiteId = 0;
                $stockId = 'stock_id';
                $collection->getSelect()->joinLeft(
                    array('_inv' => $collection->getResource()->getTable('cataloginventory_stock_status')),
                    "_inv.product_id = e.entity_id and _inv.website_id=$websiteId",
                    array('stock_status')
                );
                $collection->addExpressionAttributeToSelect('in_stock', 'IFNULL(_inv.stock_status,0)', array());
                $collection->getSelect()->reset('order');
                $collection->getSelect()->order('in_stock ASC');
            }
        } catch (\Exception $e) {
        }
        return $this;
    }
}
