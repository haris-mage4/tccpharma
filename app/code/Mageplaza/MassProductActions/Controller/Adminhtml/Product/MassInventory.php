<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_MassProductActions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\MassProductActions\Controller\Adminhtml\Product;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeHelper;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockIndexerProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Helper\Data;
use Mageplaza\MassProductActions\Logger\Logger;
use Mageplaza\MassProductActions\Model\Config\Source\InventoryCalculation;

/**
 * Class MassWebsite
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassInventory extends AbstractMassAction
{
    /**
     * @var StockConfigurationInterface
     */
    protected $_stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $_stockItemRepository;

    /**
     * @var AttributeHelper
     */
    protected $_attributeHelper;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var StockIndexerProcessor
     */
    protected $_stockIndexerProcessor;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * MassInventory constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Registry $coreRegistry
     * @param Layout $layout
     * @param Json $resultJson
     * @param ForwardFactory $resultForwardFactory
     * @param ProductResource $productResource
     * @param ProductRepositoryInterface $productRepository
     * @param ProductAction $productAction
     * @param FlatProcessor $flatProcessor
     * @param PriceProcessor $priceProcessor
     * @param Logger $logger
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param AttributeHelper $attributeHelper
     * @param DataObjectHelper $dataObjectHelper
     * @param StockIndexerProcessor $stockIndexerProcessor
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Registry $coreRegistry,
        Layout $layout,
        Json $resultJson,
        ForwardFactory $resultForwardFactory,
        ProductResource $productResource,
        ProductRepositoryInterface $productRepository,
        ProductAction $productAction,
        FlatProcessor $flatProcessor,
        PriceProcessor $priceProcessor,
        Logger $logger,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository,
        AttributeHelper $attributeHelper,
        DataObjectHelper $dataObjectHelper,
        StockIndexerProcessor $stockIndexerProcessor,
        Data $helperData
    ) {
        $this->_stockConfiguration    = $stockConfiguration;
        $this->_stockRegistry         = $stockRegistry;
        $this->_stockItemRepository   = $stockItemRepository;
        $this->_attributeHelper       = $attributeHelper;
        $this->_dataObjectHelper      = $dataObjectHelper;
        $this->_stockIndexerProcessor = $stockIndexerProcessor;
        $this->_helperData             = $helperData;

        parent::__construct(
            $context,
            $filter,
            $collectionFactory,
            $coreRegistry,
            $layout,
            $resultJson,
            $resultForwardFactory,
            $productResource,
            $productRepository,
            $productAction,
            $flatProcessor,
            $priceProcessor,
            $logger
        );
    }

    /**
     * @param AbstractCollection $collection
     *
     * @return $this|mixed
     */
    public function massAction($collection)
    {
        /** @var Http $request */
        $request             = $this->getRequest();
        $originInventoryData = $request->getPost('inventory', []);

        /** @var Result $resultBlock */
        $resultBlock = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');
        $options     = $this->_stockConfiguration->getConfigItemOptions();
        foreach ($options as $option) {
            $configOption = 'use_config_' . $option;
            if (isset($originInventoryData[$option]) && !isset($originInventoryData[$configOption])) {
                if ($option === 'enable_qty_increments') {
                    $option = 'enable_qty_inc';
                }
                $originInventoryData['use_config_' . $option] = 0;
            }
        }
        try {
            $storeId = $this->_attributeHelper->getSelectedStoreId();
            if ($originInventoryData) {
                foreach ($this->_attributeHelper->getProducts() as $product) {
                    $inventoryData = $originInventoryData;
                    /** @var StockItemInterface $stockItemDo */
                    $stockItemDo   = $this->_stockRegistry->getStockItem(
                        $product->getId(),
                        $this->_attributeHelper->getStoreWebsiteId($storeId)
                    );
                    $sourceList    = $this->_helperData->getSourceList();
                    if ($this->_helperData->versionCompare('2.3.0')
                        && $sourceList->getSize() > 1
                        && isset($inventoryData['source'])) {
                        $saveMultiple                   = $this->_helperData->createObject(
                            \Magento\Inventory\Model\ResourceModel\SourceItem\SaveMultiple::class
                        );
                        $deleteMultiple                 = $this->_helperData->createObject(
                            \Magento\Inventory\Model\ResourceModel\SourceItem\DeleteMultiple::class
                        );
                        $sourceItemFactory              = $this->_helperData->createObject(
                            \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory::class
                        );
                        $getSourceItemsBySku            = $this->_helperData->createObject(
                            \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface::class
                        );
                        $saveMultipleResourceModel      = $this->_helperData->createObject(
                            \Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\SaveMultiple::class
                        );
                        $sourceItems                    = $getSourceItemsBySku->execute($product->getSku());
                        $newSourceItems                 = [];
                        $delSourceItems                 = [];
                        $notifySourceItems              = [];
                        if ($sourceItems) {
                            foreach ($sourceItems as $key => &$sourceItem) {
                                foreach ($inventoryData['source'] as $sourceCode => $dataSource) {
                                    if (isset($dataSource['unassign'])
                                        && $sourceItem->getSourceCode() === $sourceCode) {
                                        $delSourceItems[] = $sourceItem;
                                        continue;
                                    }
                                    if ($sourceItem->getSourceCode() === $sourceCode) {
                                        $sourceItem->setStatus((int)$dataSource['status']);
                                        if ((int)$dataSource['quantity']) {
                                            $sourceItem->setQuantity($dataSource['quantity']);
                                        }
                                        if ($this->prepareDataNotifyStockQty($product, $dataSource, $sourceCode)) {
                                            $notifySourceItems[] = $this->prepareDataNotifyStockQty(
                                                $product,
                                                $dataSource,
                                                $sourceCode
                                            );
                                        }
                                        $newSourceItems[]  = $sourceItem;
                                    } elseif ($sourceItem->getSourceCode() !== $sourceCode
                                        && (int)$dataSource['quantity']) {
                                        $newSourceItem  = $sourceItemFactory->create();
                                        $sourceItemData = [
                                            'source_code' => $sourceCode,
                                            'sku'         => $product->getSku(),
                                            'quantity'    => (int)$dataSource['quantity'],
                                            'status'      => (int)$dataSource['status']
                                        ];
                                        $this->_dataObjectHelper->populateWithArray(
                                            $newSourceItem,
                                            $sourceItemData,
                                            \Magento\InventoryApi\Api\Data\SourceItemInterface::class
                                        );
                                        if ($this->prepareDataNotifyStockQty($product, $dataSource, $sourceCode)) {
                                            $notifySourceItems[] = $this->prepareDataNotifyStockQty(
                                                $product,
                                                $dataSource,
                                                $sourceCode
                                            );
                                        }
                                        $newSourceItems[] = $newSourceItem;
                                    }
                                }
                            }
                        } else {
                            foreach ($inventoryData['source'] as $sourceCode => $dataSource) {
                                if ((int)$dataSource['quantity']) {
                                    $newSourceItem  = $sourceItemFactory->create();
                                    $sourceItemData = [
                                        'source_code' => $sourceCode,
                                        'sku'         => $product->getSku(),
                                        'quantity'    => (int)$dataSource['quantity'],
                                        'status'      => (int)$dataSource['status']
                                    ];
                                    $this->_dataObjectHelper->populateWithArray(
                                        $newSourceItem,
                                        $sourceItemData,
                                        \Magento\InventoryApi\Api\Data\SourceItemInterface::class
                                    );
                                    if ($this->prepareDataNotifyStockQty($product, $dataSource, $sourceCode)) {
                                        $notifySourceItems[] = $this->prepareDataNotifyStockQty(
                                            $product,
                                            $dataSource,
                                            $sourceCode
                                        );
                                    }
                                    $newSourceItems[]    = $newSourceItem;
                                }
                            }
                        }

                        $saveMultiple->execute($newSourceItems);
                        $deleteMultiple->execute($delSourceItems);
                        $saveMultipleResourceModel->execute($notifySourceItems);
                        unset($inventoryData['source']);
                    }
                    if ($inventoryData) {
                        if (isset($inventoryData['calculation'])) {
                            /** @var array $calculation */
                            foreach ($inventoryData['calculation'] as $attr => $calculation) {
                                $newValue = $inventoryData[$attr];
                                $oldValue = (float)$stockItemDo->getData($attr);
                                switch ($calculation) {
                                    case InventoryCalculation::PLUS:
                                        $newValue = $oldValue + (float)$inventoryData[$attr];

                                        break;
                                    case InventoryCalculation::MINUS:
                                        $newValue = $oldValue - (float)$inventoryData[$attr];
                                        if ($newValue < 0) {
                                            $newValue = 0;
                                        }

                                        break;
                                }
                                $inventoryData[$attr] = $newValue;
                            }
                        }
                        if (!$stockItemDo->getProductId()) {
                            $inventoryData['product_id'] = $product->getId();
                        }
                        $stockItemId = $stockItemDo->getId();
                        $this->_dataObjectHelper->populateWithArray(
                            $stockItemDo,
                            $inventoryData,
                            StockItemInterface::class
                        );

                        $stockItemDo->setItemId($stockItemId);
                        $this->_stockItemRepository->save($stockItemDo);
                    }

                    $this->_productUpdated++;
                }
                $this->_stockIndexerProcessor->reindexList($this->_attributeHelper->getProductIds());
            }
            $this->_productUpdated = count($collection->getAllIds());
        } catch (Exception $e) {
            $resultBlock->addError(__($e->getMessage()));
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'inventory'));
    }

    /**
     * @param Product $product
     * @param array $dataSource
     * @param string $sourceCode
     *
     * @return array
     */
    public function prepareDataNotifyStockQty($product, $dataSource, $sourceCode)
    {
        $sourceItemConfigurationFactory = $this->_helperData->createObject(
            \Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterfaceFactory::class
        );
        $sourceItemConfiguration        = $sourceItemConfigurationFactory->create();
        $isNotifyQtyDefault             = isset($dataSource['notify_qty_use_default']) ? true : false;
        $sourceItemConfigurationData = [
            'source_code'      => $sourceCode,
            'sku'              => $product->getSku()
        ];
        if ((int) $dataSource['notify_stock_qty']) {
            $sourceItemConfigurationData['notify_stock_qty'] = (int) $dataSource['notify_stock_qty'];
        }
        if ($isNotifyQtyDefault) {
            $sourceItemConfigurationData['notify_stock_qty'] = null;
        }
        $this->_dataObjectHelper->populateWithArray(
            $sourceItemConfiguration,
            $sourceItemConfigurationData,
            \Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface::class
        );
        if (isset($sourceItemConfiguration['notify_stock_qty'])) {
            return $sourceItemConfiguration;
        }

        return [];
    }
}
