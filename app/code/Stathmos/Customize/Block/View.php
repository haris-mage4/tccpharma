<?php

namespace Stathmos\Customize\Block;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class View extends Template
{

    protected ProductFactory $_productFactory;

    public function __construct(
        Template\Context                                           $context,
        CollectionFactory                                          $orderCollectionFactory,
        \Magento\Customer\Model\Session                            $customerSession,
        Config                                                     $orderConfig,
        ItemFactory                                                $itemFactory,
        ProductFactory                                             $productFactory,
        array                                                      $data = []
    	)
    {
    	$this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->itemFactory = $itemFactory;
        $this->_productFactory   = $productFactory;
        parent::__construct($context, $data);
    }

    public function getOrders(){
    	$items = $this->itemFactory->create()->getCollection();
        $customerId = $this->_customerSession->getCustomerId();
        $joinConditions = 'main_table.order_id = sales_order.entity_id';
        $items->getSelect()->joinLeft(
                     ['sales_order'],
                     $joinConditions,
                     []
                    )->columns(["sales_order.customer_id","sales_order.status","sales_order.created_at","sales_order.increment_id"]);
        $items->addFieldToFilter(
                'sales_order.customer_id',
                $customerId
            )->addFieldToFilter(
                'sales_order.status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'sales_order.created_at',
                'desc'
            );
        $joinConditions = 'main_table.order_id = sales_order_payment.parent_id';
        $items->getSelect()->joinLeft(
                     ['sales_order_payment'],
                     $joinConditions,
                     []
                    )->columns("sales_order_payment.po_number");
        $joinConditions = 'main_table.product_id = catalog_product_flat_1.entity_id';
        $items->getSelect()->joinLeft(
                     ['catalog_product_flat_1'],
                     $joinConditions,
                     []
                    )->columns("catalog_product_flat_1.ndc");
        $items->getSelect()->group('main_table.sku');
        if(isset($_GET['search']) && $_GET['search']) :
        	$items->addFieldToFilter(['sales_order.increment_id', 'sales_order_payment.po_number','main_table.sku','catalog_product_flat_1.ndc','main_table.name'],
                                        [
                                            ['like' => $_GET['search']],
                                            ['like' => $_GET['search']],
                                            ['like' => $_GET['search']],
                                            ['like' => $_GET['search']],
                                            ['like' => "%".$_GET['search']."%"]
                                        ]);
    	endif;

        return $items;
    }

    public function getProductCollection() {
        return $this->_productFactory->create()->getCollection();
    }

}
