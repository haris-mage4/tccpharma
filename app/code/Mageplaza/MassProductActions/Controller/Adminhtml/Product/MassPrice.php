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
use IntlDateFormatter;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeHelper;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Logger\Logger;
use Mageplaza\MassProductActions\Model\Config\Source\Calculation;
use Zend_Filter_LocalizedToNormalized;
use Zend_Filter_NormalizedToLocalized;

/**
 * Class MassPrice
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassPrice extends AbstractMassAction
{
    /**
     * @var AttributeHelper
     */
    protected $_attributeHelper;

    /**
     * @var TimezoneInterface
     */
    protected $_timeZone;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var CatalogProduct
     */
    protected $_catalogProduct;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * MassPrice constructor.
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
     * @param AttributeHelper $attributeHelper
     * @param TimezoneInterface $timeZone
     * @param Config $eavConfig
     * @param CatalogProduct $catalogProduct
     * @param ProductFactory $productFactory
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
        AttributeHelper $attributeHelper,
        TimezoneInterface $timeZone,
        Config $eavConfig,
        CatalogProduct $catalogProduct,
        ProductFactory $productFactory
    ) {
        $this->_attributeHelper = $attributeHelper;
        $this->_timeZone        = $timeZone;
        $this->_eavConfig       = $eavConfig;
        $this->_catalogProduct  = $catalogProduct;
        $this->productFactory   = $productFactory;

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
     * @return Json|mixed
     */
    public function massAction($collection)
    {
        // phpcs:disable Magento2.Performance.ForeachArrayMerge
        /** @var Http $request */
        $request = $this->getRequest();

        /** @var array $pricesData */
        $pricesData     = $request->getParam('price', []);
        $pricesFilter   = $request->getParam('price_filter', []);
        $tierPricesData = $request->getParam('tier_price', []);
        /** @var Result $resultBlock */
        $resultBlock = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');
        try {
            $storeId = $this->_attributeHelper->getSelectedStoreId();
            if ($pricesData) {
                $this->_coreRegistry->register('mp_massproductactions_price_filter', $pricesFilter);
                $dateFormat = $this->_timeZone->getDateFormat(IntlDateFormatter::SHORT);

                /** @var array $productIds */
                foreach ($pricesData as $attributeCode => $value) {
                    $attribute = $this->_eavConfig->getAttribute(Product::ENTITY, $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($pricesData[$attributeCode]);
                        continue;
                    }
                    if ($attribute->getBackendType() === 'datetime') {
                        if (!empty($value)) {
                            $filterInput    = new Zend_Filter_LocalizedToNormalized(['date_format' => $dateFormat]);
                            $filterInternal = new Zend_Filter_NormalizedToLocalized([
                                'date_format' => DateTime::DATE_INTERNAL_FORMAT
                            ]);
                            $value          = $filterInternal->filter($filterInput->filter($value));
                        }

                        $pricesData[$attributeCode] = $value;
                    } elseif ($attribute->getFrontendInput() === 'multiselect') {
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                        $pricesData[$attributeCode] = $value;
                    }
                }

                $originalPricesData = $pricesData;
                foreach ($collection as $product) {
                    $product    = $this->productFactory->create()->load($product->getId());
                    $pricesData = $this->calculationPrice($product, $pricesFilter, $originalPricesData);
                    $this->_productAction->updateAttributes([$product->getId()], $pricesData, $storeId);
                }
            }
            if ($tierPricesData) {
                /** @var array[] $tierPricesData */
                foreach ($tierPricesData as &$tierPriceData) {
                    if (isset($tierPriceData['value_type']) && $tierPriceData['value_type'] === 'percent') {
                        $tierPriceData['percentage_value'] = $tierPriceData['price'];
                    }
                }
                unset($tierPriceData);
                foreach ($collection as $product) {
                    /** @var Product $product */
                    $oldTierPrice = $product->getTierPrice();
                    $newTierPrice = array_merge($oldTierPrice, $tierPricesData);
                    $product->setTierPrice($newTierPrice);
                    $this->_productRepository->save($product);
                }
            }
            if ($tierPricesData || $pricesData) {
                $this->_productUpdated = count($collection->getAllIds());
                $this->_flatProcessor->reindexList($collection->getAllIds());
                if ($this->_catalogProduct->isDataForPriceIndexerWasChanged($pricesData)) {
                    $this->_priceProcessor->reindexList($collection->getAllIds());
                }
            } else {
                $this->_productNonUpdated = count($collection->getAllIds());
            }
        } catch (Exception $e) {
            $resultBlock->addError(__($e->getMessage()));
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'prices'));
    }

    /**
     * @param Product $product
     * @param array $pricesFilter
     * @param array $originalPricesData
     *
     * @return mixed
     */
    public function calculationPrice($product, $pricesFilter, $originalPricesData)
    {
        if ($pricesFilter['price']['type']
            && $pricesFilter['price']['type'] !== Calculation::FIXED_VALUE) {
            $usingCost   = $pricesFilter['price']['using_cost'];
            $changePrice = $usingCost ? $product->getCost() : $product->getPrice();
            switch ($pricesFilter['price']['type']) {
                case 'plus':
                    $originalPricesData['price'] += $changePrice;
                    break;
                case 'plus-percent':
                    $originalPricesData['price'] = $changePrice + (($changePrice / 100) * $originalPricesData['price']);
                    break;
                case 'minus':
                    $originalPricesData['price'] = $changePrice - $originalPricesData['price'];
                    break;
                case 'minus-percent':
                    $originalPricesData['price'] = $changePrice - (($changePrice / 100) * $originalPricesData['price']);
                    break;
            }
        }

        if ($pricesFilter['cost']['type']) {
            $changePrice = $product->getCost();
            switch ($pricesFilter['cost']['type']) {
                case 'plus':
                    $originalPricesData['cost'] += $changePrice;
                    break;
                case 'plus-percent':
                    $originalPricesData['cost'] = $changePrice + (($changePrice / 100) * $originalPricesData['cost']);
                    break;
                case 'minus':
                    $originalPricesData['cost'] = $changePrice - $originalPricesData['cost'];
                    break;
                case 'minus-percent':
                    $originalPricesData['cost'] = $changePrice - (($changePrice / 100) * $originalPricesData['cost']);
                    break;
            }
        }

        if ($pricesFilter['special_price']['type']
            && $pricesFilter['special_price']['type'] !== Calculation::FIXED_VALUE) {
            $usingPrice   = $pricesFilter['special_price']['using_price'];
            $specialPrice = $usingPrice ? $product->getPrice() : $product->getSpecialPrice();
            switch ($pricesFilter['special_price']['type']) {
                case 'plus':
                    $originalPricesData['special_price'] += $specialPrice;
                    break;
                case 'plus-percent':
                    $originalPricesData['special_price'] =
                        $specialPrice + (($specialPrice / 100) * $originalPricesData['special_price']);
                    break;
                case 'minus':
                    $originalPricesData['special_price'] = $specialPrice - $originalPricesData['special_price'];
                    break;
                case 'minus-percent':
                    $originalPricesData['special_price'] =
                        $specialPrice - (($specialPrice / 100) * $originalPricesData['special_price']);
                    break;
            }
        }

        return $originalPricesData;
    }
}
