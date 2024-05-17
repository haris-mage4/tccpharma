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
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Api\MassProductLinkInterface;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Logger\Logger;
use Mageplaza\MassProductActions\Model\Config\Source\Direction;

/**
 * Class AbstractMassAction
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
abstract class AbstractLinkProduct extends AbstractMassAction implements MassProductLinkInterface
{
    /**
     * @var ProductLinkInterfaceFactory
     */
    protected $_productLinkFact;

    /**
     * @var array
     */
    protected $_removeSkuLinks = [];

    /**
     * @var array
     */
    protected $_addSkuLinks = [];

    /**
     * @var array
     */
    protected $_copySkuLinks = [];

    /**
     * @var array
     */
    protected $_updateSkuLinks = [];

    /**
     * @var array
     */
    protected $_selectedProductSKUs = [];

    /**
     * AbstractLinkProduct constructor.
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
     * @param ProductLinkInterfaceFactory $productLinkFact
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
        ProductLinkInterfaceFactory $productLinkFact
    ) {
        $this->_productLinkFact = $productLinkFact;

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
     * @param array $data
     * @param Collection $collection
     *
     * @return $this|mixed
     */
    protected function updateLinkProduct($data, $collection)
    {
        /** @var Result $resultBlock */
        $resultBlock = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');

        /** Remove product sku links */
        if ($data['action'] == 1) {
            if (isset($data['related'])) {
                $removeSkuLinks = [];
                foreach ($collection as $item) {
                    $relatedProducts = $item->getRelatedProducts();
                    foreach ($relatedProducts as $relatedProduct) {
                        $removeSkuLinks[] = $relatedProduct->getSku();
                    }
                    $this->_removeSkuLinks = $removeSkuLinks;
                }
            }

            if (isset($data['cross_sell'])) {
                $removeSkuLinks = [];
                foreach ($collection as $item) {
                    $crossSellProducts = $item->getCrossSellProducts();
                    foreach ($crossSellProducts as $crossSellProduct) {
                        $removeSkuLinks[] = $crossSellProduct->getSku();
                    }
                    $this->_removeSkuLinks = $removeSkuLinks;
                }
            }

            if (isset($data['up_sell'])) {
                $removeSkuLinks = [];
                foreach ($collection as $item) {
                    $upSellProducts = $item->getUpSellProducts();
                    foreach ($upSellProducts as $upSellProduct) {
                        $removeSkuLinks[] = $upSellProduct->getSku();
                    }
                    $this->_removeSkuLinks = $removeSkuLinks;
                }
            }
        } else {
            if (!$data['remove_products'] && !$data['add_products'] && !$data['copy_products']) {
                $resultBlock->addError(__('You have not selected any products'));

                return $this->_resultJson->setData($this->_addAjaxResult($resultBlock));
            }

            if ($removeSkuLinks = $data['remove_products']) {
                $removeProducts = $this->_collectionFactory->create()->addFieldToFilter('sku', ['in' => $removeSkuLinks]);
                $removeSkuLinks = [];
                foreach ($removeProducts as $removeProduct) {
                    $removeSkuLinks[] = $removeProduct->getSku();
                }
                $this->_removeSkuLinks = $removeSkuLinks;
            }
        }

        /** Add product sku links */
        if ($addSkuLinks = $data['add_products']) {
            $addProducts = $this->_collectionFactory->create()->addFieldToFilter('sku', ['in' => $addSkuLinks]);
            $addSkuLinks = [];
            foreach ($addProducts as $addProduct) {
                $addSkuLinks[] = $addProduct->getSku();
            }
            $this->_addSkuLinks = $addSkuLinks;
        }

        /** Copy product sku links */
        if ($copySkuLinks = $data['copy_products']) {
            $copyProducts = $this->_collectionFactory->create()->addFieldToFilter('sku', ['in' => $copySkuLinks]);
            $copySkuLinks = [];
            foreach ($copyProducts as $copyProduct) {
                $copySkuLinks[] = $copyProduct->getSku();
            }
            $this->_copySkuLinks = $this->_getCopiedLinkProductSku($copySkuLinks);
        }

        if (empty($this->_removeSkuLinks) && empty($this->_addSkuLinks) && empty($this->_copySkuLinks)) {
            $resultBlock->addError(__('Please check your selected SKU again'));

            return $this->_resultJson->setData($this->_addAjaxResult($resultBlock));
        }

        /** Get update product sku links */
        $this->_updateSkuLinks = array_unique(array_merge($this->_addSkuLinks, $this->_copySkuLinks));

        $this->_saveProductLinks($collection, $resultBlock);

        if ((int) $data['direction'] === Direction::MUTUAL_WAY) {
            $this->_updateReserveProductLink($resultBlock);
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'product links'));
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    abstract public function getCurrentLinkProductSku($product);

    /**
     * @param array $newSkuLinks
     * @param Product $product
     *
     * @return array
     */
    abstract public function getProductLinkData($newSkuLinks, $product);

    /**
     * @param Product $product
     * @param Product $linkedProduct
     * @param int $position
     *
     * @return array
     */
    abstract public function generateProductLink($product, $linkedProduct, $position);

    /**
     * @param array $copySkuLinks
     *
     * @return array
     */
    protected function _getCopiedLinkProductSku($copySkuLinks)
    {
        $newSkuLinks = [];
        foreach ($copySkuLinks as $copySkuLink) {
            /** @var Product $copyLinkProduct */
            $copyLinkProduct = $this->_getProductBySku($copySkuLink);
            if ($copyLinkProduct) {
                $currentLinkProdSKUs = $this->getCurrentLinkProductSku($copyLinkProduct);
                foreach ($currentLinkProdSKUs as $sku) {
                    $newSkuLinks[] = $sku;
                }
            }
        }

        return $newSkuLinks;
    }

    /**
     * @param Collection $collection
     * @param Result $resultBlock
     *
     */
    protected function _saveProductLinks($collection, $resultBlock)
    {
        foreach ($collection->getItems() as $product) {
            /** @var Product $product */
            $newSkuLinks = [];
            $isRemoved   = false;
            if ($this->_removeSkuLinks) {
                $isRemoved   = true;
                $newSkuLinks = array_udiff(
                    $this->getCurrentLinkProductSku($product),
                    $this->_removeSkuLinks,
                    'strcasecmp'
                );
            }
            $newSkuLinks = $newSkuLinks || $isRemoved ? $newSkuLinks : $this->getCurrentLinkProductSku($product);

            if ($this->_updateSkuLinks) {
                $newSkuLinks = array_unique(array_merge(
                    $newSkuLinks,
                    $this->_updateSkuLinks
                ));
                if ($newSkuLinks) {
                    $this->_saveProductLinksFinish($newSkuLinks, $product, $resultBlock);
                } else {
                    $this->_productNonUpdated++;
                }
            } else {
                $this->_saveProductLinksFinish($newSkuLinks, $product, $resultBlock);
            }
            $this->_selectedProductSKUs[] = $product->getSku();
        }
    }

    /**
     * @param array $newSkuLinks
     * @param Product $product
     * @param Result $resultBlock
     */
    protected function _saveProductLinksFinish($newSkuLinks, $product, $resultBlock)
    {
        $linkDataAll = $this->getProductLinkData($newSkuLinks, $product);

        $product->setProductLinks($linkDataAll);
        try {
            $this->_productRepository->save($product);
            $this->_productUpdated++;
        } catch (Exception $e) {
            $resultBlock->addError(__($e->getMessage()));
            $this->_productNonUpdated++;
        }
    }

    /**
     * @param Result $resultBlock
     */
    protected function _updateReserveProductLink($resultBlock)
    {
        /** @var array $selectedProductSKUs */
        $isRemove = array_diff($this->_removeSkuLinks, $this->_addSkuLinks);
        $isAdd    = array_diff($this->_addSkuLinks, $this->_removeSkuLinks);
        if ($isRemove) {
            $this->_saveReserveProductLink($isRemove, $resultBlock, 'delete');
        }

        if ($isAdd) {
            $this->_saveReserveProductLink($isAdd, $resultBlock, 'add');
        }
    }

    /**
     * @param array $skuLinks
     * @param Result $resultBlock
     * @param string $type
     */
    protected function _saveReserveProductLink($skuLinks, $resultBlock, $type)
    {
        foreach ($skuLinks as $skuLink) {
            /** @var Product $product */
            $product = $this->_getProductBySku($skuLink);
            if ($product) {
                $newSkuLinks = ($type === 'delete') ? array_udiff(
                    $this->getCurrentLinkProductSku($product),
                    $this->_selectedProductSKUs,
                    'strcasecmp'
                ) : array_unique(array_merge(
                    $this->getCurrentLinkProductSku($product),
                    $this->_selectedProductSKUs
                ));
                $product->setProductLinks($this->getProductLinkData(
                    $newSkuLinks,
                    $product
                ));
                try {
                    $this->_productResource->save($product);
                    $this->_productUpdated++;
                } catch (Exception $e) {
                    $resultBlock->addError(__($e->getMessage()));
                    $this->_productNonUpdated++;
                }
            }
        }
    }
}
