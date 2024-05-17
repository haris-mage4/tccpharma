<?php

namespace Stathmos\Customize\Plugin\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class ProductRepository
{
    private TimezoneInterface $timezoneInterface;
    private IndexerFactory $indexerFactory;
    private TypeListInterface $cacheTypeList;
    private Pool $cacheFrontendPool;

    /**
     * @param TimezoneInterface $timezoneInterface
     * @param IndexerFactory $indexerFactory
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        TimezoneInterface $timezoneInterface,
        IndexerFactory $indexerFactory,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->timezoneInterface = $timezoneInterface;
        $this->indexerFactory = $indexerFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    public function aroundSave(
        \Magento\Catalog\Model\ProductRepository $subject,
        callable $proceed,
        ProductInterface $product,
        $saveOptions = false
    ) {
        $objDate = $this->timezoneInterface;
        $date = $objDate->date()->format('Y-m-d');

        $logFileName = "log_".$date.".log";
        $sku = $product->getSku();

        $mode = (!file_exists(BP . '/var/PRODUCT_UPDATE_DO_NOT_DELETE/'.$logFileName)) ? 'w' : 'a';

        $myfile = fopen(BP . '/var/PRODUCT_UPDATE_DO_NOT_DELETE/'.$logFileName, $mode) or die("Unable to open file!");
        $txt = "Product With SKU : ".$product->getSku() . " Is Processing."."\n";
        fwrite($myfile, $txt);

        $result = $proceed($product, $saveOptions);

        // Reindex specific nodes
        $indexerIds = ['catalog_product_attribute', 'catalog_product_price'];
        foreach ($indexerIds as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }

        // Flush cache
        $this->cacheTypeList->cleanType('config');
        $this->cacheTypeList->cleanType('layout');
        $this->cacheTypeList->cleanType('block_html');
        $this->cacheTypeList->cleanType('full_page');
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }

        $txt = "Product With SKU : ".$product->getSku() . " Is index/Updated."."\n";
        fwrite($myfile, $txt);
        fclose($myfile);

        return $result;
    }
}

