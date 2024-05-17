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

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeHelper;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Logger\Logger;

/**
 * Class MassAttribute
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassAttribute extends AbstractMassAction
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
     * MassAttribute constructor.
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
        CatalogProduct $catalogProduct
    ) {
        $this->_attributeHelper = $attributeHelper;
        $this->_timeZone        = $timeZone;
        $this->_eavConfig       = $eavConfig;
        $this->_catalogProduct  = $catalogProduct;

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
        $request = $this->getRequest();

        /** @var array $attributesData */
        $attributesData   = $request->getParam('attributes', []);
        $attributesFilter = $request->getParam('attributes_filter', []);

        /** @var Result $resultBlock */
        $resultBlock = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');
        try {
            $storeId = $this->_attributeHelper->getSelectedStoreId();
            unset($attributesData['product_has_weight']);
            if ($attributesData) {
                if ($attributesFilter) {
                    $this->_coreRegistry->register('mp_massproductactions_attributes_filter', $attributesFilter);
                }
                /** @var array $productIds */
                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = $this->_eavConfig
                        ->getAttribute(Product::ENTITY, $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    if ($attribute->getBackendType() === 'datetime') {
                        if (!empty($value)) {
                            try {
                                $value = $this->convertTimeZone($value);
                            } catch (Exception $e) {
                                throw new LocalizedException(__('Please enter the correct date format.'));
                            }
                        }

                        $attributesData[$attributeCode] = $value;
                    } elseif ($attribute->getFrontendInput() === 'multiselect') {
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                        $attributesData[$attributeCode] = $value;
                    }
                }

                if (isset($attributesData['custom_design_from'])
                    && $attributesData['custom_design_from']
                    && isset($attributesData['custom_design_to'])
                    && $attributesData['custom_design_to']) {
                    $value    = strtotime($attributesData['custom_design_from']);
                    $maxValue = strtotime($attributesData['custom_design_to']);
                    if ($value > $maxValue) {
                        throw new LocalizedException(
                            __('Make sure the To Date is later than or the same as the From Date.')
                        );
                    }
                }

                if (isset($attributesData['news_from_date'])
                    && $attributesData['news_from_date']
                    && isset($attributesData['news_to_date'])
                    && $attributesData['news_to_date']) {
                    $value    = strtotime($attributesData['news_from_date']);
                    $maxValue = strtotime($attributesData['news_to_date']);
                    if ($value > $maxValue) {
                        throw new LocalizedException(
                            __('Make sure the To Date is later than or the same as the From Date.')
                        );
                    }
                }

                $allIds = $collection->getAllIds();

                $this->_productAction
                    ->updateAttributes($allIds, $attributesData, $storeId);
                $this->_productUpdated = $collection->getSize();
                $this->_flatProcessor->reindexList($allIds);
                if ($this->_catalogProduct->isDataForPriceIndexerWasChanged($attributesData)) {
                    $this->_priceProcessor->reindexList($allIds);
                }
            }
        } catch (Exception $e) {
            $this->_productNonUpdated = $collection->getSize();
            $resultBlock->addError(__($e->getMessage()));
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'attributes'));
    }

    /**
     * @param string $date
     * @param string $format
     *
     * @return string
     * @throws Exception
     */
    public function convertTimeZone($date, $format = 'm/d/Y')
    {
        $dateTime = new DateTime($date, new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone($this->_timeZone->getConfigTimezone()));

        return $dateTime->format($format);
    }
}
