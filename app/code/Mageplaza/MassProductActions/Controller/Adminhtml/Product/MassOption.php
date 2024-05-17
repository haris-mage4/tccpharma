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
use Magento\Backend\Helper\Js;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\OptionFactory as ProductOptFact;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option as ProductOptResource;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory as ProductOptColFact;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Logger\Logger;

/**
 * Class MassOption
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassOption extends AbstractMassAction
{
    /**
     * @var Js
     */
    protected $_jsHelper;

    /**
     * @var ProductOptFact
     */
    protected $_productOptFact;

    /**
     * @var ProductOptResource
     */
    protected $_productOptResource;

    /**
     * @var ProductOptColFact
     */
    protected $_productOptColFact;

    /**
     * MassOption constructor.
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
     * @param Js $jsHelper
     * @param ProductOptFact $productOptFact
     * @param ProductOptResource $productOptResource
     * @param ProductOptColFact $productOptColFact
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
        Js $jsHelper,
        ProductOptFact $productOptFact,
        ProductOptResource $productOptResource,
        ProductOptColFact $productOptColFact
    ) {
        $this->_jsHelper           = $jsHelper;
        $this->_productOptFact     = $productOptFact;
        $this->_productOptResource = $productOptResource;
        $this->_productOptColFact  = $productOptColFact;

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
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function massAction($collection)
    {
        /** @var Http $request */
        $request = $this->getRequest();
        /** @var Result $resultBlock */
        $resultBlock = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');
        if ($data = $request->getPost('products')) {
            $options        = [];
            $parentRecordId = 0;
            $childRecordId  = 0;
            $productIds     = array_keys($this->_jsHelper->decodeGridSerializedInput($data));
            /** Get current product custom options */
            foreach ($productIds as $productId) {
                $product       = $this->_productRepository->getById($productId);
                $customOptions = $this->_productOptColFact->create()->getProductOptions($product->getId(), 0);
                foreach ($customOptions as $customOption) {
                    /** @var Option $option */
                    $option = [
                        'type'           => $customOption->getType(),
                        'is_require'     => $customOption->getIsRequire(),
                        'sku'            => $customOption->getSku(),
                        'max_characters' => $customOption->getMaxCharacters(),
                        'file_extension' => $customOption->getFileExtension(),
                        'image_size_x'   => $customOption->getImageSizeX(),
                        'image_size_y'   => $customOption->getImageSizeY(),
                        'title'          => $customOption->getTitle(),
                        'price'          => $customOption->getPrice(),
                        'price_type'     => $customOption->getPriceType(),
                        'record_id'      => (string) $parentRecordId,
                        'sort_order'     => $customOption->getSortOrder()
                    ];
                    if ($values = $customOption->getValues()) {
                        foreach ($values as $value) {
                            $option['values'][] = [
                                'sku'        => $value->getSku(),
                                'sort_order' => $value->getSortOrder(),
                                'title'      => $value->getTitle(),
                                'price'      => $value->getPrice(),
                                'price_type' => $value->getPriceType(),
                                'record_id'  => (string) $childRecordId
                            ];
                            $childRecordId++;
                        }
                    }
                    $options[] = $option;
                    $parentRecordId++;
                }
            }
            /** Copy the custom options to the selected products */
            foreach ($collection->getItems() as $product) {
                /** @var Product $product */
                $product->setHasOptions(1);
                $product->setCanSaveCustomOptions(true);

                try {
                    foreach ($options as $arrayOption) {
                        /** @var Option $option */
                        $option = $this->_productOptFact->create();
                        $option->setProductId($product->getId())
                            ->setStoreId(0)
                            ->addData($arrayOption);
                        $this->_productOptResource->save($option);
                    }
                    $this->_productRepository->save($product);
                    $this->_productUpdated++;
                } catch (Exception $e) {
                    $resultBlock->addError(__($e->getMessage()));
                    $this->_productNonUpdated++;
                }
            }
        } else {
            $resultBlock->addError(__('There is no update. You have not selected any products.'));
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock));
    }
}
