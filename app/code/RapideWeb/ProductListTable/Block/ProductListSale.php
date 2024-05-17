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
use Magento\Catalog\Helper\Image;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

class ProductListSale extends \Magento\Catalog\Block\Product\ListProduct
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
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_productStatus;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
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
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * Initialize
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \RapideWeb\ProductListTable\Helper\Data $helper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
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

    /**
     * Get Product collection in store
     */
    public function getAllProductsListTable()
    {
        $limit = 30;
        $queryChar = $this->getQueriedChar();

        $categories[] = 45;

        $storeId = $this->getStoreId();
        $collection = $this->_collection
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', array('eq' => 1))
            ->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]) // fetch visibility products
            ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
            ->addStoreFilter($storeId);

        if ( $queryChar && $queryChar != 'all' ) {
            $collection->addAttributeToFilter('name', array('like' => $queryChar . '%'));
        }

        $collection->setOrder('name', 'ASC');
        $collection->setPageSize($limit);

        $this->_productCollection = $collection;
        return $this->_productCollection;
    }

    /**
     * Get Queried Character
     */
    public function getQueriedChar() {
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
     *@see \Magento\Catalog\Model\Product\Image
     */
    public function resizeImage($product, $imageType, $width, $height = null)
    {
        return $this->_imageHelper
            ->init($product, $imageType)
            ->constrainOnly(TRUE)
            ->keepAspectRatio(TRUE)
            ->keepTransparency(TRUE)
            ->keepFrame(FALSE)
            ->resize($width, $height);
    }

    /**
     * Get Formatted Price from decimal
     * @param float $number
     * @param int $decimals
     * @return string
     */
    public function getFormattedPrice($number, $decimals = 2) {
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
    public function getToolbarHtml() {
        return $this->getChildHtml('pager');
    }

    /**
     * Whether redirect to cart enabled
     *
     * @return bool
     */
    public function isRedirectToCartEnabled(): bool
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            ScopeInterface::SCOPE_STORE
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
    public function isEnabled(): bool
    {
        return $this->helper->isEnabled();
    }

    /**
     * Get Product Listing Table Page Url
     * @return string
     */
    public function getPageTitle(){
        return $this->helper->getPageTitle();
    }

    /**
     * Get Product Listing Table Page Url
     * @return string
     */
    public function isShowNavigation(){
        return $this->helper->isShowNavigation();
    }

    /**
     * Get Show/Hide Product Image Value
     * @return boolean
     */
    public function isShowProductImage(){
        return $this->helper->isShowProductImage();
    }

    /**
     * Get Show/Hide Product Option Value
     * @return boolean
     */
    public function isShowProductOption(){
        return $this->helper->isShowProductOption();
    }

    /**
     * Get Categories
     *
     */
    public function getCategories(){
        return $this->helper->getCategories();
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getImageWidth(){
        return $this->helper->getImageWidth();
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getImageHeight(){
        return $this->helper->getImageHeight();
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getButtonText(){
        return $this->helper->getButtonText();
    }

    /**
     * Get Add to Cart button Background color
     * @return string
     */
    public function getButtonBgColor(){
        return $this->helper->getButtonBgColor();
    }

    /**
     * Get Add to Cart button Text color
     * @return string
     */
    public function getButtonColor(){
        return $this->helper->getButtonColor();
    }
}
