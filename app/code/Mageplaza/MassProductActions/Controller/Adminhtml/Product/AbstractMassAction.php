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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Api\MassActionInterface;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Logger\Logger;

/**
 * Class AbstractMassAction
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
abstract class AbstractMassAction extends Action implements MassActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Layout
     */
    protected $_layout;

    /**
     * @var Json
     */
    protected $_resultJson;

    /**
     * @var ForwardFactory
     */
    protected $_resultFwFactory;

    /**
     * @var ProductResource
     */
    protected $_productResource;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var ProductAction
     */
    protected $_productAction;

    /**
     * @var FlatProcessor
     */
    protected $_flatProcessor;

    /**
     * @var PriceProcessor
     */
    protected $_priceProcessor;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var int
     */
    protected $_productUpdated = 0;

    /**
     * @var int
     */
    protected $_productNonUpdated = 0;

    /**
     * @var array
     */
    protected $_skuNotFound = [];

    /**
     * AbstractMassAction constructor.
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
        Logger $logger
    ) {
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry      = $coreRegistry;
        $this->_layout            = $layout;
        $this->_resultJson        = $resultJson;
        $this->_resultFwFactory   = $resultForwardFactory;
        $this->_productResource   = $productResource;
        $this->_productRepository = $productRepository;
        $this->_productAction     = $productAction;
        $this->_flatProcessor     = $flatProcessor;
        $this->_priceProcessor    = $priceProcessor;
        $this->_logger            = $logger;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|mixed
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        if (!$request->isAjax()) {
            return $this->_resultFwFactory->create()->forward('noroute');
        }
        if (!$request->isPost()) {
            throw new NotFoundException(__('Page not found.'));
        }

        /** @var AbstractCollection $collection */
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        if (empty($collection->getItems())) {
            /** @var Result $resultBlock */
            $resultBlock = $this->_layout
                ->createBlock(Result::class)
                ->setTemplate('Mageplaza_MassProductActions::result.phtml');
            $resultBlock->addError(__('The selected product(s) does not exist.'));
            $result = [
                'status'      => true,
                'result_html' => $resultBlock->toHtml()
            ];

            return $this->_resultJson->setData($result);
        }

        return $this->massAction($collection);
    }

    /**
     * @param AbstractCollection $collection
     *
     * @return mixed
     */
    abstract public function massAction($collection);

    /**
     * Add ajax result
     *
     * @param Result $resultBlock
     * @param string $type
     *
     * @return array
     */
    protected function _addAjaxResult($resultBlock, $type = '')
    {
        if ($this->_productUpdated) {
            $updateMessage = __('A total of %1 product(s) have been updated %2.', $this->_productUpdated, $type);
            $resultBlock->addSuccess($updateMessage);
            $this->_logger->info($updateMessage);
        }
        if ($this->_productNonUpdated) {
            $nonUpdateMessage = __(
                'A total of %1 product(s) have not been updated %2.',
                $this->_productNonUpdated,
                $type
            );
            $resultBlock->addError($nonUpdateMessage);
            $this->_logger->info($nonUpdateMessage);
        }
        if ($this->_skuNotFound) {
            $sku = implode(',', array_unique($this->_skuNotFound));
            $resultBlock->addError(__('The product with SKU %1 is not found', $sku));
        }
        $result = [
            'status'      => (bool) $this->_productUpdated,
            'result_html' => $resultBlock->toHtml()
        ];

        return $result;
    }

    /**
     * @param string $sku
     *
     * @return bool|ProductInterface
     */
    protected function _getProductBySku($sku)
    {
        try {
            $product = $this->_productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            $this->_logger->info($e->getMessage());
            $product              = false;
            $this->_skuNotFound[] = $sku;
        }

        return $product;
    }
}
