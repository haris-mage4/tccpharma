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
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Logger\Logger;

/**
 * Class MassCategory
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassCategory extends AbstractMassAction
{
    /**
     * @var CategoryLinkManagementInterface
     */
    protected $_catLinkManagement;

    /**
     * MassCategory constructor.
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
     * @param CategoryLinkManagementInterface $catLinkManagement
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
        CategoryLinkManagementInterface $catLinkManagement
    ) {
        $this->_catLinkManagement = $catLinkManagement;

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
        $data    = $request->getPost('product');

        /** @var Result $resultBlock */
        $resultBlock      = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');
        $addCategories    = $data['add_category_ids'] ? explode(',', $data['add_category_ids']) : [];
        $removeCategories = $data['remove_category_ids'] ? explode(',', $data['remove_category_ids']) : [];
        foreach ($collection->getItems() as $product) {
            /** @var Product $product */
            $currentCategories = $product->getCategoryIds();
            $removedCategories = $removeCategories
                ? array_diff($currentCategories, $removeCategories)
                : $currentCategories;

            $addedCategories = $addCategories
                ? array_unique(array_merge($removedCategories, $addCategories))
                : $removedCategories;

            if ($currentCategories === $addedCategories) {
                $this->_productNonUpdated++;
                continue;
            }
            try {
                $this->_catLinkManagement->assignProductToCategories(
                    $product->getSku(),
                    $addedCategories
                );
                $this->_productUpdated++;
            } catch (Exception $e) {
                $resultBlock->addError(__($e->getMessage()));
            }
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'categories'));
    }
}
