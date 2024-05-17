<?php

namespace Gunstore\InventoryCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;

class OrderPlaceAddRemarkHistory implements ObserverInterface {

    private StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver;
    private GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority;
    private DefaultSourceProviderInterface $defaultSourceProvider;
    private SourceItemsSaveInterface $_sourceItemsSaveInterface;
    private SourceItemInterfaceFactory $_sourceItemFactory;
    private GetSourceItemsDataBySku $sourceDataBySku;

    public function __construct(
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        SourceItemInterfaceFactory $sourceItemFactory,
        GetSourceItemsDataBySku $sourceDataBySku
    ) {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->_sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->_sourceItemFactory = $sourceItemFactory;
        $this->sourceDataBySku = $sourceDataBySku;
    }

    public function execute(Observer $observer ) {

        $order = $observer->getEvent()->getOrder();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Order_place_create.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $websiteId = $order->getStore()->getWebsiteId();

        $stockId = $this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();
        $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute((int)$stockId);

        if (!empty($sources)) {
            $sourceCode = $sources[0]->getSourceCode();
        } else {
            $sourceCode = $this->defaultSourceProvider->getCode();
        }

        foreach ($order->getAllItems() as $item) {

            $itemSku = $item->getSku();
            $itemQty = $item->getQtyOrdered();

            $skuInventoryData = $this->sourceDataBySku->execute($itemSku);

            if($skuInventoryData & !empty($skuInventoryData)){
                foreach ($skuInventoryData as $key => $stockData) {
                    $sourceInventory = $stockData['source_code'];
                    if($sourceInventory == 'ggsstock'){

                        $sourceQty = $stockData['quantity'];

                        if($sourceQty > 0){

                            $sourceInventory = $stockData['source_code'];
                            $sourceStatus = $stockData['status'];
                            $sourceQty = $stockData['quantity'];
                            break;;
                        }

                    } else {
                        if($sourceInventory == $sourceCode){
                            $sourceInventory = $stockData['source_code'];
                            $sourceStatus = $stockData['status'];
                            $sourceQty = $stockData['quantity'];
                        }
                    }
                }
                $logger->info('source inventory ==> '.$sourceInventory);
                $logger->info('source qty ==> '.$sourceQty);
                if($sourceQty <= 0){
                    $itemQty = 0;
                }
                $sourceItem = $this->_sourceItemFactory->create();
                $sourceItem->setSourceCode($sourceInventory);
                $sourceItem->setSku($itemSku);
                $sourceItem->setQuantity($sourceQty - $itemQty);
                $sourceItem->setStatus($sourceStatus);
                $this->_sourceItemsSaveInterface->execute([$sourceItem]);
                $logger->info('Order Increment Id = '.$order->getIncrementId().' = Sku = '.$itemSku.' = Source = '.$sourceInventory." = Total Item Qty = ".$sourceQty." = Qty Ship = ".$itemQty);
            }
        }
    }
}
