<?php

namespace RapideWeb\ProductListTable\Block;

/**
 * @company RapideWeb
 * @package RapideWeb_ProductListTable
 * @author James Wu <james4u.boda@gmail.com>
 * @date Mar 30, 2018
 *
 */

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ActionInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Url\Helper\Data;

class ProductList extends ListProduct
{
    /**
     * Product collection model
     *
     */
    protected $_collection;

    /**
     * Product collection model
     */
    protected $_productCollection;

    /**
     * Image helper
     *
     * @var Image
     */
    protected $_imageHelper;

    /**
     * Catalog Layer
     *
     * @var Resolver
     */
    protected $_catalogLayer;

    /**
     * @var PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var Data
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Status
     */
    protected $_productStatus;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * RapideWeb ProductListTable Helper Data
     *
     * @var \RapideWeb\ProductListTable\Helper\Data
     */
    protected $helper;

    /**
     * Price Helper Data
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

    /**
     * Initialize
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        Collection $collection,
        Status $productStatus,
        Visibility $productVisibility,
        \RapideWeb\ProductListTable\Helper\Data $helper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->imageBuilder = $context->getImageBuilder();
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_collection = $collection;
        $this->_imageHelper = $context->getImageHelper();
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        $this->helper = $helper;
        $this->priceHelper = $priceHelper;
        $this->urlEncoder = $urlEncoder;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }


    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl("https://shop.tccpharma.com/productlisttable/"),
            ]
        ];
    }
    /**
     * Get Product collection in store
     */
    public function getAllProductsListTable()
    {
        $limit = 20;
        $queryChar = $this->getQueriedChar();

        $categories = explode(",", $this->getCategories());

        $storeId = $this->getStoreId();
        $collection = $this->_collection
            ->addAttributeToSelect(['name'])
            ->addAttributeToFilter('status', array('eq' => 1))
            ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
            ->addStoreFilter($storeId);

        if ($queryChar && $queryChar != 'all') {
            $collection->addAttributeToFilter('name', array('like' => $queryChar . '%'));
        }

        if (!empty($categories)) {
            $collection->addCategoriesFilter(array('in' => $categories));
        }

        $collection->setOrder('name', 'ASC');
        $collection->setPageSize($limit);
        $this->_productCollection = $collection;
        return $this->_productCollection;
    }

    /**
     * Get Queried Character
     */
    public function getQueriedChar()
    {
        return $this->helper->getQueryChar();
    }

    /**
     * Get Query Url
     */
    public function getQueryUrl($param)
    {
        return $this->getUrl('productlisttable', ['_query' => ['query' => $param]]);
    }

    /**
     * Schedule resize of the image
     * $width *or* $height can be null - in this case, lacking dimension will be calculated.
     *
     * @param int $width
     * @param int $height
     * @return Image
     *@see Product\Image
     */
    public function resizeImage($product, $imageType, $width, $height = null)
    {
        return $this->_imageHelper
            ->init($product, $imageType)
            ->constrainOnly(true)
            ->keepAspectRatio(true)
            ->keepTransparency(true)
            ->keepFrame(false)
            ->resize($width, $height);
    }

    /**
     * Get Formatted Price from decimal
     * @param float $number
     * @param int $decimals
     * @return string
     */
    public function getFormattedPrice($number, $decimals = 2)
    {
        return $this->priceHelper->currency(number_format($number, $decimals), true, false);
    }

    /**
     * Get customizable option add to cart url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getCustomizableToCartUrl($product)
    {
        $uenc = $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl());
        return $this->getUrl('checkout/cart/add', ['uenc' => $uenc, 'product' => $product->getId()]);
    }

    /**
     * Get product toolbar
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Whether redirect to cart enabled
     *
     * @return bool
     */
    public function isRedirectToCartEnabled()
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get store identifier
     *
     * @return  int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Check if store is active
     *
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function isStoreActive()
    {
        return $this->_storeManager->getStore()->isActive();
    }

    /**
     * Get Enabled/Disabled Extension Value
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * Get Product Listing Table Page Url
     * @return string
     */
    public function getPageTitle()
    {
        return $this->helper->getPageTitle();
    }

    /**
     * Get Product Listing Table Page Url
     * @return string
     */
    public function isShowNavigation()
    {
        return $this->helper->isShowNavigation();
    }

    /**
     * Get Show/Hide Product Image Value
     * @return boolean
     */
    public function isShowProductImage()
    {
        return $this->helper->isShowProductImage();
    }

    /**
     * Get Show/Hide Product Option Value
     * @return boolean
     */
    public function isShowProductOption()
    {
        return $this->helper->isShowProductOption();
    }

    /**
     * Get Categories
     *
     */
    public function getCategories()
    {
        return $this->helper->getCategories();
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getImageWidth()
    {
        return $this->helper->getImageWidth();
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getImageHeight()
    {
        return $this->helper->getImageHeight();
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getButtonText()
    {
        return $this->helper->getButtonText();
    }

    /**
     * Get Add to Cart button Background color
     * @return string
     */
    public function getButtonBgColor()
    {
        return $this->helper->getButtonBgColor();
    }

    /**
     * Get Add to Cart button Text color
     * @return string
     */
    public function getButtonColor()
    {
        return $this->helper->getButtonColor();
    }
}
