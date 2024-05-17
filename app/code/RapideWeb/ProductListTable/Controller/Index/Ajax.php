<?php

namespace RapideWeb\ProductListTable\Controller\Index;

/**
 * @company RapideWeb
 * @package RapideWeb_ProductListTable
 * @author James Wu <james4u.boda@gmail.com>
 * @date Mar 30, 2018
 *
 */

use Eagle\Tooltip\Helper\Config\TooltipConfig;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use RapideWeb\ProductListTable\Helper\Data;

class Ajax extends Action
{
    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * HTTP Request
     *
     * @var Http
     */
    protected Http $request;

    /**
     * RapideWeb ProductListTable Helper Data
     *
     * @var Data
     */
    protected Data $helper;

    protected TooltipConfig $tooltipHelper;

    private CacheInterface $cacheInterface;

    private SerializerInterface $serializerInterface;
    private Output $outputHelper;
    private StoreManagerInterface $_storeManager;
    private \Magento\Framework\Pricing\Helper\Data $priceHelper;
    private Visibility $_productVisibility;
    private Status $_productStatus;

    private Collection $_collection;

    private Session $session;

    private \Magento\Framework\App\Http\Context $httpContext;

    private FormKey $formKey;

    private ResourceConnection $resourceConnection;

    private Stock $stock;

    private Layout $layout;
    private Product $product;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Http $request
     * @param Collection $collection
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param Data $helper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param StoreManagerInterface $storeManager
     * @param Output $outputHelper
     * @param TooltipConfig $tooltipHelper
     * @param CacheInterface $cacheInterface
     * @param SerializerInterface $serializerInterface
     * @param Session $session
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param FormKey $formKey
     * @param ResourceConnection $resourceConnection
     * @param Stock $stock
     * @param Layout $layout
     * @param Collection $_collection
     * @param Product $product
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Http $request,
        Collection $collection,
        Status $productStatus,
        Visibility $productVisibility,
        Data $helper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        StoreManagerInterface $storeManager,
        Output $outputHelper,
        TooltipConfig $tooltipHelper,
        CacheInterface $cacheInterface,
        SerializerInterface $serializerInterface,
        Session $session,
        \Magento\Framework\App\Http\Context $httpContext,
        FormKey $formKey,
        ResourceConnection $resourceConnection,
        Stock $stock,
        Layout $layout,
        Collection $_collection,
        Product $product
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->outputHelper = $outputHelper;
        $this->_storeManager = $storeManager;
        $this->_collection = $collection;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        $this->helper = $helper;
        $this->priceHelper = $priceHelper;
        $this->tooltipHelper = $tooltipHelper;
        $this->cacheInterface = $cacheInterface;
        $this->serializerInterface = $serializerInterface;
        $this->session = $session;
        $this->httpContext = $httpContext;
        $this->formKey = $formKey;
        $this->resourceConnection = $resourceConnection;
        $this->stock = $stock;
        $this->layout = $layout;
        $this->_collection = $_collection;
        parent::__construct($context);
        $this->product = $product;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $allowedGroups = [0, 1, 6, 8];

        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') != 'xmlhttprequest') {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('productlisttable');
        }

        $block = $this->layout->createBlock('RapideWeb\ProductListTable\Block\ProductList');
        $btnText = $block->getButtonText();

        $priceHelper = $this->priceHelper;
        $salePage = false;
        if ($this->getRequest()->getParam('sale') && $this->getRequest()->getParam('sale') == 1) {
            $salePage = true;
        }

        $cache = $this->cacheInterface;
        $serializer = $this->serializerInterface;
        $data = [];

        if ($data && count($data)) {
            $count = $data;
        } else {
            $count = $this->getAllProducts();
            $storeData = $cache->save(
                $serializer->serialize($count),
                'datatable-all-product-count',
                ['DATATABLE-ALL-PRODUCT-COUNT'],
                86400
            );
        }
        $collection = $this->getAllProductsListTable();
        $data = [];
        $data['draw'] = $_GET['draw'];
        $data['recordsTotal'] = $count['total'];
        $data['recordsFiltered'] = $collection->getSize();
        $data['data'] = [];

        $collection->setPageSize($_GET['length']); // fetch 10 products

        $page = ($_GET['start'] / $_GET['length']) + 1;

        $collection->setCurPage($page);

        foreach ($collection as $_product) {
            $sku = '<strong class="product name product-item-name"><a class="product-item-link"href="' . $_product->getProductUrl() . '">' . $this->outputHelper->productAttribute($_product, $_product->getSku(), 'sku') . '</a></strong>';

            $ndc = '<strong class="product name product-item-name"><a class="product-item-link"href="' . $_product->getProductUrl() . '">' . $this->outputHelper->productAttribute($_product, $_product->getNdc(), 'ndc') . '</a></strong>';

            $name = '<strong class="product name product-item-name"><a class="product-item-link"href="' . $_product->getProductUrl() . '">' . $this->outputHelper->productAttribute($_product, $_product->getName(), 'name') . '</a></strong>';

            $man = '<strong class="product name product-item-name">' . $this->outputHelper->productAttribute($_product, $_product->getManufacturer(), 'manufacturer') . '</strong>';

            $size = '<strong class="product name product-item-name">' . $this->outputHelper->productAttribute($_product, $_product->getSize(), 'size') . '</strong>';

            $userContext = $this->httpContext;
            $isLoggedIn = $userContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

            $url  = $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            $login_url = $this->_url->getUrl('customer/account/login', array('referer' => base64_encode($url)));

            $customerSession = $this->session;
            $_product = $this->product->load($_product->getId());
            $stockItem = $_product->getExtensionAttributes()->getStockItem($_product->getId());
            $quantity = $stockItem->getQty();
            if ($isLoggedIn) {
                $price = '';

                if ($this->tooltipHelper->hasAdvancedPrice($_product)) {
                    if ($this->tooltipHelper->getLowestTierPriceForCustomerGroup($_product->getSku()) != null) {
                        if ($priceHelper->currency($_product->getFinalPrice(), true, false) == '$0.01') {
                            $price = '<div class="tooltip-container">';
                            $price .= '<div class="tooltip-icon baseuser">';
                            $price .= '<span class="tooltip-question-mark">?</span>';
                            $price .= '</div>';
                            $price .= '<div class="tooltip-content">Request a quote.</div>';
                        } else {
                            $price = $this->getStr($priceHelper, $_product, $price);
                        }
                        $price .= '</div>';
                    }
                } else {
                    if ($priceHelper->currency($_product->getFinalPrice(), true, false) == '$0.01') {
                        $price .= '<div class="tooltip-container">';
                        $price .= '<div class="tooltip-icon">';
                        $price .= '<span class="tooltip-question-mark">?</span>';
                        $price .= '</div>';
                        $price .= '<div class="tooltip-content">' . $this->tooltipHelper->getRegularPriceText() . '</div>';
                    } else {
                        $price = $this->getStr($priceHelper, $_product, $price);
                    }
                    $price .= '</div>';
                };
            } else {
                $price = "<a href='$login_url'>Log in</a>  to see price";
            }

            // $qty = '<div class="qty-wrapper"><select name="qty" onchange="updateQty(this)" product_id="'.$_product->getId().'" class="input-text qty" id="qty-a-'.$_product->getId().'" style="width:50px"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>';
            if ($quantity != 0) :
                $qty = '<div class="qty-wrapper"><p class="product_available"><div class="tooltip"><span style="color:green;font-weight:bold;text-transform: capitalize;">In Stock</span> <i class="fa fa-question-circle" aria-hidden="true"></i><span class="tooltiptext"><strong>In Stock:</strong> In stock at our AZ warehouse. Instant turnaround likely.<br></span></div></p></div>';
            else :
                $qty = '<div class="qty-wrapper"><div class="tooltip"><span style="color:blue;font-weight:bold;text-transform: capitalize;">In Network</span> <i class="fa fa-question-circle" aria-hidden="true"></i><span class="tooltiptext"><strong>In Network:</strong><ul class="tooltiptext_list">    <li>Product may be available to ship with a 1-5 day lead time.</li><li> Request a quote for more specific information.</li></ul><p class="note">*If unavailable, we will quote an equivalent option or can notify you of availability.</p></span></div></div>';
            endif;
            // $checkbox = '<div class="qty-wrapper"><input name="do_not_substitute" value="1" type="checkbox"/><label>Do Not Substitute</label></div>';
            $addToCart = '';
            if ($isLoggedIn) {
                $customerGroup = $customerSession->getCustomer()->getGroupId();
                if ($_product->isSaleable()) :
                    $postParams = $block->getAddToCartPostParams($_product);
                    $action = $postParams['action'];
                    $addToCart .= '<form data-role="tocart-form" id="product-form-' . $_product->getId() . '" data-product-sku="' . $block->escapeHtml($_product->getSku()) . '"action="' . $action . '"method="post">';
                    $addToCart .= '<input type="hidden" name="product" value="' . $postParams['data']['product'] . '">';
                    $addToCart .= '<input type="hidden" name="uenc" value="' . $postParams['data']['uenc'] . '">';
                    $addToCart .= '<input type="hidden" name="qty" maxlength="12" value="1" title="Qty" class="input-text qty qty-' . $_product->getId() . '" />';
                    $addToCart .= $block->getBlockHtml('formkey');
                    if (in_array($customerGroup, $allowedGroups)) {
                        $addToCart .= '<button type="submit" title="' . $btnText . '" class="action tocart primary"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span>' . $btnText . '</span></button></form>';
                    } else {
                        $addToCart .= '<a type="submit" href="javascript:addToQuote(' . $_product->getId() . ')" item="' . $_product->getId() . '" title="Add to Quote" class="amquote-addto-button action outline" data-amquote-js="addto-button" id="product-addtoquote-' . $_product->getId() . '"><span class="amquote-addto-button-text">Add to Quote</span></a></form>';
                    }
                else :
                    if ($_product->isAvailable()) :
                        $addToCart .= '<div class="stock available"><span>In stock</span></div>';
                    else :
                        $addToCart .= '<div class="stock unavailable"><span>Out of stock</span></div>';
                    endif;
                endif;
            } else {
                $addToCart = "<a href='$login_url'>Log in</a> to add to cart";
            }
            $data['data'][] = [
                $sku,
                $ndc,
                $name,
                $man,
                $size,
                $price,
                $qty,
                $addToCart
            ];
        }

        echo json_encode($data);
    }

    /**
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAllProductsListTable(): Collection
    {
        $limit = 10;
        $queryChar = $this->getQueriedChar();

        $categories[] = 45;
        $salePage = false;
        if ($this->getRequest()->getParam('sale') && $this->getRequest()->getParam('sale') == 1) {
            $salePage = true;
        }

        $storeId = $this->getStoreId();
        $collection = $this->_collection
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]) // fetch visibility products
            ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
            ->addStoreFilter($storeId)
            ->addFinalPrice();
        if ($queryChar && $queryChar != 'all') {
            $collection->addAttributeToFilter('name', array('like' => $queryChar . '%'));
        }

        if ($this->request->getParam('category')) {
            $collection->addCategoriesFilter(['in' => $this->request->getParam('category')]);
        }
        if ($this->request->getParam('size')) {
            $collection->addAttributeToFilter('size', array('like' => $this->request->getParam('size') . '%'));
        }
        if ($this->request->getParam('manufacturer')) {
            $collection->addAttributeToFilter('manufacturer', array('like' => $this->request->getParam('manufacturer') . '%'));
        }
        if ($this->request->getParam('availability')) {
            $_stock = $this->stock;
            $_stock->addInStockFilterToCollection($collection);
        }
        if (isset($_GET['search']['value']) && $_GET['search']['value']) {
            $query = $_GET['search']['value'];
            if (preg_match('/\b(with|and)\b/i', $query)) {
                $query = str_replace("with", "", $query);
                $query = str_replace("and", "", $query);
                $query = preg_replace('/\s+/', ' ', $query);
            }

            $terms = explode(' ', $query);
            $filterConditions = [];
            foreach ($terms as $term) {
                $filterConditions[] = ['like' => '%' . $term . '%'];
            }

            if (strpos($query, '#') === 0) {
                $filterConditions[] = ['like' => '%' . $query . '%'];
                $collection->addAttributeToFilter('description', $filterConditions);
            } else {
                $collection->addAttributeToFilter(
                    [
                        // ['attribute' => 'name', $filterConditions],
                        ['attribute' => 'name', 'like' => '%' . $query . '%'],
                        ['attribute' => 'sku', 'like' => '%' . $query . '%'],
                        ['attribute' => 'ndc', 'like' => '%' . $query . '%'],
                        ['attribute' => 'manufacturer', 'like' => '%' . $query . '%'],
                    ]
                );
            }
        }

        if ($this->getRequest()->getParam('inStockOnly') == 1) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
            $collection->addAttributeToFilter('qty', ['gt' => 0]);
        }



        if ($salePage) {
            $collection->addCategoriesFilter(array('in' => $categories));
        }

        $pricefrom = $this->request->getParam('from_price');
        $pricefrom = str_replace('$', '', $pricefrom);
        if (!$pricefrom) {
            $pricefrom = 0;
        }
        $priceto = $this->request->getParam('from_to');
        $priceto = str_replace('$', '', $priceto);
        if (!$priceto) {
            $priceto = 99999999;
        }
        $collection->getSelect()->where("price_index.final_price > " . $pricefrom)->where("price_index.final_price < " . $priceto);
        $order = $this->getRequest()->getParam('order');
        if ($order && isset($order[0]) && isset($order[0]['column']) && $order[0]['column'] == 5) {
            $collection->setOrder('price', $order[0]['dir']);
        } else {
            $collection->setOrder('name', 'ASC');
        }
        return $collection;
    }

    /**
     * @return array
     */
    public function getAllProducts()
    {
        $queryChar = $this->getQueriedChar();
        $salePage = false;
        if ($this->getRequest()->getParam('sale') && $this->getRequest()->getParam('sale') == 1) {
            $salePage = true;
        }
        $categories[] = 45;
        $catId = 2;
        if ($salePage) {
            $catId = 45;
        }

        $sql = $mainsql = "SELECT `e`.`sku` FROM `catalog_product_entity` AS `e`
            INNER JOIN `catalog_product_entity_int` AS `at_status_default` ON (`at_status_default`.`entity_id` = `e`.`entity_id`) AND (`at_status_default`.`attribute_id` = '97') AND `at_status_default`.`store_id` = 0
            LEFT JOIN `catalog_product_entity_int` AS `at_status` ON (`at_status`.`entity_id` = `e`.`entity_id`) AND (`at_status`.`attribute_id` = '97') AND (`at_status`.`store_id` = 1)
            INNER JOIN `catalog_category_product_index_store1` AS `cat_index` ON cat_index.product_id=e.entity_id AND cat_index.store_id=1 AND cat_index.visibility IN(3, 2, 4) AND cat_index.category_id=" . $catId . "
            INNER JOIN `catalog_product_entity_varchar` AS `at_name_default` ON (`at_name_default`.`entity_id` = `e`.`entity_id`) AND (`at_name_default`.`attribute_id` = '73') AND `at_name_default`.`store_id` = 0
            LEFT JOIN `catalog_product_entity_varchar` AS `at_name` ON (`at_name`.`entity_id` = `e`.`entity_id`) AND (`at_name`.`attribute_id` = '73') AND (`at_name`.`store_id` = 1)";
        if ($queryChar && $queryChar != 'all') {
            $sql = $mainsql . " WHERE (IF(at_status.value_id > 0, at_status.value, at_status_default.value) = 1) AND (IF(at_status.value_id > 0, at_status.value, at_status_default.value) IN(1)) AND (IF(at_name.value_id > 0, at_name.value, at_name_default.value) LIKE '" . $queryChar . "%')";
        }

        $resourceConnection = $this->resourceConnection;
        $results = $resourceConnection->getConnection()->fetchAll($sql);
        $count['total'] = count($results);

        return $count;
    }

    public function getQueriedChar()
    {
        $this->request->getParams();
        return $this->request->getParam('query');
    }


    /**
     * Get store identifier
     *
     * @return  int
     * @throws NoSuchEntityException
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return $this->_storeManager->getStore()->getId();
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
    public function getImageWidth(): string
    {
        return $this->helper->getImageWidth();
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getImageHeight(): string
    {
        return $this->helper->getImageHeight();
    }

    /**
     * Get Add to Cart button Text color
     * @return string
     */
    public function getButtonColor(): string
    {
        return $this->helper->getButtonColor();
    }

    /**
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param Product $_product
     * @param string $price
     * @return string
     */
    public function getStr(\Magento\Framework\Pricing\Helper\Data $priceHelper, Product $_product, string $price): string
    {
        $price .= '' . $priceHelper->currency($_product->getFinalPrice(), true, false) . '<br/>';
        $price .= '<div class="tooltip-container">';
        $price .= '<div class="tooltip-icon">';
        $price .= '<span class="tooltip-question-mark">?</span>';
        $price .= '</div>';
        $price .= '<div class="tooltip-content">' . $this->tooltipHelper->getRegularPriceText() . '</div>';
        return $price;
    }
}
