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

namespace Mageplaza\MassProductActions\Controller\Adminhtml\Product\Grid;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Layout;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;

/**
 * Class SubmitProducts
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product\Grid
 */
class SubmitProducts extends Action
{
    /**
     * @var ForwardFactory
     */
    protected $_forwardFactory;

    /**
     * @var Js
     */
    protected $_jsHelper;

    /**
     * @var CollectionFactory
     */
    protected $_productColFact;

    /**
     * @var Layout
     */
    protected $_layout;

    /**
     * @var Json
     */
    protected $_resultJson;

    /**
     * SubmitProducts constructor.
     *
     * @param Context $context
     * @param ForwardFactory $forwardFactory
     * @param Js $jsHelper
     * @param CollectionFactory $productColFact
     * @param Layout $layout
     * @param Json $resultJson
     */
    public function __construct(
        Context $context,
        ForwardFactory $forwardFactory,
        Js $jsHelper,
        CollectionFactory $productColFact,
        Layout $layout,
        Json $resultJson
    ) {
        $this->_forwardFactory = $forwardFactory;
        $this->_jsHelper       = $jsHelper;
        $this->_productColFact = $productColFact;
        $this->_layout         = $layout;
        $this->_resultJson     = $resultJson;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|mixed
     * @throws NotFoundException
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        if (!$request->isAjax()) {
            return $this->_forwardFactory->create()->forward('noroute');
        }
        if (!$request->isPost()) {
            throw new NotFoundException(__('Page not found.'));
        }
        /** @var Result $resultBlock */
        $resultBlock       = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');
        $submittedProducts = '';
        if ($data = $request->getPost('products')) {
            $productSku = [];
            $productIds = array_keys($this->_jsHelper->decodeGridSerializedInput($data));
            /** @var Collection $collection */
            $collection = $this->_productColFact->create()
                ->addStoreFilter()
                ->addAttributeToSelect('sku')
                ->addFieldToFilter('entity_id', $productIds);
            foreach ($collection as $product) {
                /** @var Product $product */
                $productSku[] = $product->getSku();
            }
            $submittedProducts = implode(',', $productSku);
            $resultBlock->addSuccess(__('You have submitted the products successfully.'));
        } else {
            $resultBlock->addError(__('You have not selected any products.'));
        }
        $result = [
            'status'      => true,
            'products'    => $submittedProducts,
            'result_html' => $resultBlock->toHtml()
        ];

        return $this->_resultJson->setData($result);
    }
}
