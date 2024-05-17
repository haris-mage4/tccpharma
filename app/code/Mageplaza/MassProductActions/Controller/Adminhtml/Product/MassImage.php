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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\Product\Gallery\Processor as GalleryProcessor;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery as ProductGallery;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Store\Model\Store;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Block\Adminhtml\Result;
use Mageplaza\MassProductActions\Logger\Logger;
use Mageplaza\MassProductActions\Model\Config\Source\ImageActions;

/**
 * Class MassImage
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product
 */
class MassImage extends AbstractMassAction
{
    const IMAGE_PATH = 'pub/media/catalog/product';

    /**
     * @var GalleryProcessor
     */
    protected $_galleryProcessor;

    /**
     * @var ProductGallery
     */
    protected $_productGallery;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * MassImage constructor.
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
     * @param GalleryProcessor $galleryProcessor
     * @param ProductGallery $productGallery
     * @param Config $eavConfig
     * @param ProductFactory $productFactory
     * @param Filesystem $filesystem
     * @param File $fileDriver
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
        GalleryProcessor $galleryProcessor,
        ProductGallery $productGallery,
        Config $eavConfig,
        ProductFactory $productFactory,
        Filesystem $filesystem,
        File $fileDriver
    ) {
        $this->_productGallery   = $productGallery;
        $this->_galleryProcessor = $galleryProcessor;
        $this->eavConfig         = $eavConfig;
        $this->productFactory    = $productFactory;
        $this->filesystem        = $filesystem;
        $this->fileDriver        = $fileDriver;

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
     * @param Collection $collection
     *
     * @return $this|mixed
     * @throws LocalizedException
     */
    public function massAction($collection)
    {
        /** @var Http $request */
        $request     = $this->getRequest();
        $imageAction = $request->getPost('image')['action'];
        /** @var Result $resultBlock */
        $resultBlock = $this->_layout
            ->createBlock(Result::class)
            ->setTemplate('Mageplaza_MassProductActions::result.phtml');
        /** Remove all product images */
        if ((int) $imageAction === ImageActions::REMOVE_IMAGES) {
            $collection->addMediaGalleryData();
            $this->_removeAllImages($collection, $resultBlock);

            return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'images'));
        }

        /** Update product images */
        if ($productSKUs = $request->getPost('image')['product_sku']) {
            $this->_updateProductImages($collection, $resultBlock, $productSKUs);
        } else {
            $resultBlock->addError(__('You have not selected any products, so no product has been updated'));
        }

        return $this->_resultJson->setData($this->_addAjaxResult($resultBlock, 'images'));
    }

    /**
     * @param Collection $collection
     * @param Result $resultBlock
     *
     * @throws LocalizedException
     */
    protected function _removeAllImages($collection, $resultBlock)
    {
        /** @var Product $product */
        foreach ($collection->getItems() as $product) {
            $_product = $this->productFactory->create()->load($product->getId());
            if ($this->isProductAttributeExists('mp_gridview')) {
                $mpGridView = $_product->getData('mp_gridview');
                $product->setData('mp_gridview', $mpGridView);
            }

            $mediaGalleryImages = $product->getMediaGalleryImages();

            if (count($mediaGalleryImages) > 0) {
                foreach ($mediaGalleryImages as $image) {
                    $this->_productGallery->deleteGallery($image->getValueId());
                    $this->_galleryProcessor->removeImage($_product, $image->getFile());
                    $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('catalog/product') . $image->getFile();
                    if ($this->fileDriver->isExists($mediaPath)) {
                        $this->fileDriver->deleteFile($mediaPath);
                    }
                }
                $product->setMediaGalleryEntries([]);
            }

            try {
                $product->save();
                $this->_productUpdated++;
            } catch (Exception $e) {
                $resultBlock->addError(__($e->getMessage()));
                $this->_productNonUpdated++;
            }
        }
    }

    /**
     * @param Collection $collection
     * @param Result $resultBlock
     * @param string $productSKUs
     */
    protected function _updateProductImages($collection, $resultBlock, $productSKUs)
    {
        $productSkuArr = explode(',', $productSKUs);
        $addedImages   = [];

        foreach ($productSkuArr as $productSKU) {
            /** @var Product $product */
            $product = $this->_getProductBySku($productSKU);
            if ($product) {
                $galleryImages = $product->getMediaGalleryImages();
                foreach ($galleryImages as $image) {
                    if ($image->getMediaType() === 'image') {
                        $addedImages[] = $image->getPath();
                    }
                }
            }
        }
        if ($addedImages) {
            $addedImages = array_reverse($addedImages);
            foreach ($collection->getItems() as $product) {
                /** @var Product $product */
                try {
                    $product = $this->compatibleCatalogPermission($product);

                    foreach ($addedImages as $addedImage) {
                        $product->addImageToMediaGallery(
                            $addedImage,
                            ['image', 'small_image', 'thumbnail'],
                            false,
                            false
                        );
                        $this->_productResource->save($product);
                    }
                    $this->_productUpdated++;
                } catch (Exception $e) {
                    $resultBlock->addError(__($e->getMessage()));
                    $this->_productNonUpdated++;
                }
            }
        } else {
            $resultBlock->addError(__('There are no product that is match with your selection'));
        }
    }

    /**
     * @param Product $product
     *
     * @return Product
     * @throws NoSuchEntityException
     */
    public function compatibleCatalogPermission($product)
    {
        /** Compatible with catalog permission */
        $product->setStoreId(Store::DEFAULT_STORE_ID);
        $oldProduct = $this->_productRepository->getById(
            $product->getId(),
            false,
            Store::DEFAULT_STORE_ID
        );

        $product->setData('mpcp_isactive', $oldProduct->getData('mpcp_isactive'));
        $product->setData('mpcp_startdate', $oldProduct->getData('mpcp_startdate'));
        $product->setData('mpcp_enddate', $oldProduct->getData('mpcp_enddate'));
        $product->setData('mpcp_customergroup', $oldProduct->getData('mpcp_customergroup'));
        $product->setData('mpcp_redirectto', $oldProduct->getData('mpcp_redirectto'));
        $product->setData('mpcp_usecf_redirectto', $oldProduct->getData('mpcp_usecf_redirectto'));
        $product->setData('mpcp_selectcmspage', $oldProduct->getData('mpcp_selectcmspage'));
        $product->setData('mpcp_hideaction', $oldProduct->getData('mpcp_hideaction'));
        $product->setData('mpcp_usecf_hideaction', $oldProduct->getData('mpcp_usecf_hideaction'));

        return $product;
    }

    /**
     * @param mixed $field
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isProductAttributeExists($field)
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $field);

        return ($attr && $attr->getId()) ? true : false;
    }
}
