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
use Magento\Catalog\Model\Product\OptionFactory;
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
 * Class Save
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Option
 */
class Save extends Action
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var OptionFactory
     */
    protected $option;

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
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param ProductCollection $productCollection
     * @param Filter $filter
     * @param OptionFactory $option
     * @param Layout $layout
     * @param Logger $logger ,
     * @param JsonResponse $resultJson
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        ProductCollection $productCollection,
        Filter $filter,
        OptionFactory $option,
        Layout $layout,
        Logger $logger,
        JsonResponse $resultJson
    ) {
        $this->filter            = $filter;
        $this->productRepository = $productRepository;
        $this->option            = $option;
        $this->layout            = $layout;
        $this->logger            = $logger;
        $this->resultJson        = $resultJson;
        $this->productCollection = $productCollection;
        parent::__construct($context);
    }

    /**
     * Save custom options
     *
     * @return ResponseInterface|JsonResponse|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $options = $this->_request->getPost('options');

        $collection = $this->filter->getCollection($this->productCollection->create()->addFieldToSelect('*'));

        /** @var Result $resultBlock */
        $resultBlock = $this->layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');

        if (!$options) {
            $emptyMessage = __('Please add option before submit');
            $resultBlock->addError($emptyMessage);

            $result = [
                'empty'       => true,
                'result_html' => $resultBlock->toHtml()
            ];

            return $this->resultJson->setData($result);
        }

        $productUpdated     = 0;
        $invalidProductType = 0;

        foreach ($collection as $product) {
            try {
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

                $currentProduct = $this->productRepository->getById($product->getId());

                foreach ($options as $option) {
                    unset($option['option_id']);
                    $addOptions = $this->option->create()
                        ->setProductId($currentProduct->getId())
                        ->setStoreId($currentProduct->getStoreId());
                    $addOptions->addData($option)->save();
                    $currentProduct->addOption($addOptions);
                }
                $this->productRepository->save($currentProduct);
                $productUpdated++;
            } catch (Exception $e) {
                $nonUpdateMessage = __('Something went wrong when add option');
                $resultBlock->addError($nonUpdateMessage);
                $this->logger->info($e->getMessage());

                $result = [
                    'result_html' => $resultBlock->toHtml()
                ];

                return $this->resultJson->setData($result);
            }
        }

        if ($invalidProductType) {
            $errorMessage = __("Please do not select grouped or bundled products with dynamic pricing to add custom options");
            $resultBlock->addError($errorMessage);
        }

        if ($productUpdated) {
            $updateMessage = __('A total of %1 product(s) have added custom options.', $productUpdated);
            $resultBlock->addSuccess($updateMessage);
        }

        $result = [
            'result_html' => $resultBlock->toHtml()
        ];

        return $this->resultJson->setData($result);
    }
}
