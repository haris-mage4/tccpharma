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

namespace Mageplaza\MassProductActions\Controller\Adminhtml\Option;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json as JsonResponse;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Logger\Logger;

/**
 * Class Remove
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Option
 */
class Remove extends Action
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var JsonResponse
     */
    protected $resultJson;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param Filter $filter
     * @param Layout $layout
     * @param ProductCollection $productCollection
     * @param Logger $logger ,
     * @param JsonResponse $resultJson
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        Filter $filter,
        Layout $layout,
        ProductCollection $productCollection,
        Logger $logger,
        JsonResponse $resultJson
    ) {
        $this->productRepository = $productRepository;
        $this->productCollection = $productCollection;
        $this->layout            = $layout;
        $this->logger            = $logger;
        $this->resultJson        = $resultJson;
        $this->filter            = $filter;
        parent::__construct($context);
    }

    /**
     * Remove custom options
     *
     * @return ResponseInterface|JsonResponse|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->productCollection->create()->addFieldToSelect('*'));

        /** @var Result $resultBlock */
        $resultBlock = $this->layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');

        $invalidProductType = 0;
        $productRemoved     = 0;
        $productNoOptions   = 0;

        foreach ($collection as $product) {
            try {
                $currentProduct = $this->productRepository->getById($product->getId());
                if ($product->getTypeId() == 'grouped') {
                    $invalidProductType++;
                    continue;
                }
                if ($product->getTypeId() == 'bundle') {
                    if (!$product->getPriceType()) {
                        $invalidProductType++;
                        continue;
                    }
                }
                $productOptions = $currentProduct->getOptions();
                if ($productOptions && count($productOptions)) {
                    foreach ($productOptions as $option) {
                        $option->delete();
                    }
                    $productRemoved++;
                    $currentProduct->setHasOption(0)->save();
                } else {
                    $productNoOptions++;
                }
            } catch (Exception $e) {
                $nonUpdateMessage = __('Something went wrong when remove option');
                $resultBlock->addError($nonUpdateMessage);
                $this->logger->info($e->getMessage());

                $result = [
                    'result_html' => $resultBlock->toHtml()
                ];

                return $this->resultJson->setData($result);
            }
        }

        if ($invalidProductType) {
            $errorMessage = __("Please do not select grouped or bundle products to remove custom options");
            $resultBlock->addError($errorMessage);
        }
        if ($productRemoved) {
            $updateMessage = __('A total of %1 product(s) have been removed all custom options.', $productRemoved);
            $resultBlock->addSuccess($updateMessage);
        }
        if ($productNoOptions) {
            $errorMessage = __("There are a total of %1 product(s) with no custom options to remove",
                $productNoOptions);
            $resultBlock->addError($errorMessage);
        }
        $result = [
            'result_html' => $resultBlock->toHtml()
        ];

        return $this->resultJson->setData($result);
    }
}
