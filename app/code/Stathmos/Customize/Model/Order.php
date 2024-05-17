<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Stathmos\Customize\Model;

use Magento\Config\Model\Config\Source\Nooptreq;
use Magento\Directory\Model\Currency;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\ProductOption;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection as CreditmemoCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection as PaymentCollection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection as ShipmentCollection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as TrackCollection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection as HistoryCollection;
use Magento\Store\Model\ScopeInterface;

/**
 * Order model
 *
 * Supported events:
 *  sales_order_load_after
 *  sales_order_save_before
 *  sales_order_save_after
 *  sales_order_delete_before
 *  sales_order_delete_after
 *
 * @api
 * @method int getGiftMessageId()
 * @method \Magento\Sales\Model\Order setGiftMessageId(int $value)
 * @method bool hasBillingAddressId()
 * @method \Magento\Sales\Model\Order unsBillingAddressId()
 * @method bool hasShippingAddressId()
 * @method \Magento\Sales\Model\Order unsShippingAddressId()
 * @method int getShippigAddressId()
 * @method bool hasCustomerNoteNotify()
 * @method bool hasForcedCanCreditmemo()
 * @method bool getIsInProcess()
 * @method \Magento\Customer\Model\Customer|null getCustomer()
 * @method \Magento\Sales\Model\Order setSendEmail(bool $value)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Order extends \Magento\Sales\Model\Order
{
    const ENTITY = 'order';

    /**
     * Order states
     */
    const STATE_NEW = 'new';

    const STATE_PENDING_PAYMENT = 'pending_payment';

    const STATE_PROCESSING = 'processing';

    const STATE_COMPLETE = 'complete';

    const STATE_CLOSED = 'closed';

    const STATE_CANCELED = 'canceled';

    const STATE_HOLDED = 'holded';

    const STATE_PAYMENT_REVIEW = 'payment_review';

    /**
     * Order statuses
     */
    const STATUS_FRAUD = 'fraud';

    /**
     * Order flags
     */
    const ACTION_FLAG_CANCEL = 'cancel';

    const ACTION_FLAG_HOLD = 'hold';

    const ACTION_FLAG_UNHOLD = 'unhold';

    const ACTION_FLAG_EDIT = 'edit';

    const ACTION_FLAG_CREDITMEMO = 'creditmemo';

    const ACTION_FLAG_INVOICE = 'invoice';

    const ACTION_FLAG_REORDER = 'reorder';

    const ACTION_FLAG_SHIP = 'ship';

    const ACTION_FLAG_COMMENT = 'comment';

    /**
     * Report date types
     */
    const REPORT_DATE_TYPE_CREATED = 'created';

    const REPORT_DATE_TYPE_UPDATED = 'updated';

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order';

    /**
     * @var string
     */
    protected $_eventObject = 'order';

    /**
     * @var InvoiceCollection
     */
    protected $_invoices;

    /**
     * @var TrackCollection
     */
    protected $_tracks;

    /**
     * @var ShipmentCollection
     */
    protected $_shipments;

    /**
     * @var CreditmemoCollection
     */
    protected $_creditmemos;

    /**
     * @var array
     */
    protected $_relatedObjects = [];

    /**
     * @var Currency
     */
    protected $_orderCurrency = null;

    /**
     * @var Currency|null
     */
    protected $_baseCurrency = null;

    /**
     * Array of action flags for canUnhold, canEdit, etc.
     *
     * @var array
     */
    protected $_actionFlag = [];

    /**
     * Flag: if after order placing we can send new email to the customer.
     *
     * @var bool
     */
    protected $_canSendNewEmailFlag = true;

    /**
     * Identifier for history item
     *
     * @var string
     */
    protected $entityType = 'order';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     * @deprecated 100.1.0 Remove unused dependency.
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productListFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $_orderItemCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceManagement;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    protected $_orderHistoryFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory
     */
    protected $_addressCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory
     */
    protected $_paymentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory
     */
    protected $_historyCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    protected $_shipmentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory
     */
    protected $_memoCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    protected $_trackCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesOrderCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var ProductOption
     */
    private $productOption;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ScopeConfigInterface;
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Order\Config $orderConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Order\Status\HistoryFactory $orderHistoryFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productListFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param ResolverInterface $localeResolver
     * @param ProductOption|null $productOption
     * @param OrderItemRepositoryInterface $itemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productListFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ResolverInterface $localeResolver = null,
        ProductOption $productOption = null,
        OrderItemRepositoryInterface $itemRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        ScopeConfigInterface $scopeConfig = null
    ) {
        
        $this->localeResolver = $localeResolver ?: ObjectManager::getInstance()->get(ResolverInterface::class);
        $this->productOption = $productOption ?: ObjectManager::getInstance()->get(ProductOption::class);
        $this->itemRepository = $itemRepository ?: ObjectManager::getInstance()
            ->get(OrderItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $timezone,
            $storeManager,
            $orderConfig,
            $productRepository,
            $orderItemCollectionFactory,
            $productVisibility,
            $invoiceManagement,
            $currencyFactory,
            $eavConfig,
            $orderHistoryFactory,
            $addressCollectionFactory,
            $paymentCollectionFactory,
            $historyCollectionFactory,
            $invoiceCollectionFactory,
            $shipmentCollectionFactory,
            $memoCollectionFactory,
            $trackCollectionFactory,
            $salesOrderCollectionFactory,
            $priceCurrency,
            $productListFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order::class);
    }

   

    /**
     * Get sales Order collection model populated with data
     *
     * @param array $filters
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected function getSalesOrderCollection(array $filters = [])
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $salesOrderCollection */
        $salesOrderCollection = $this->salesOrderCollectionFactory->create();
        foreach ($filters as $field => $condition) {
            $salesOrderCollection->addFieldToFilter($field, $condition);
        }
        return $salesOrderCollection->load();
    }

   

   

    /**
     * Check whether order could be canceled by states and flags
     *
     * @return bool
     */
    protected function _canVoidOrder()
    {
        return !($this->isCanceled() || $this->canUnhold() || $this->isPaymentReview());
    }

    

    

    /**
     * Retrieve credit memo for zero total refunded availability.
     *
     * @param float $totalRefunded
     * @return bool
     */
    private function canCreditmemoForZeroTotalRefunded($totalRefunded)
    {
        $isRefundZero = abs($totalRefunded) < .0001;
        // Case when Adjustment Fee (adjustment_negative) has been used for first creditmemo
        $hasAdjustmentFee = abs($totalRefunded - $this->getAdjustmentNegative()) < .0001;
        $hasActionFlag = $this->getActionFlag(self::ACTION_FLAG_EDIT) === false;
        if ($isRefundZero || $hasAdjustmentFee || $hasActionFlag) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve credit memo for zero total availability.
     *
     * @param float $totalRefunded
     * @return bool
     */
    private function canCreditmemoForZeroTotal($totalRefunded)
    {
        $totalPaid = $this->getTotalPaid();
        //check if total paid is less than grandtotal
        $checkAmtTotalPaid = $totalPaid <= $this->getGrandTotal();
        //case when amount is due for invoice
        $hasDueAmount = $this->canInvoice() && ($checkAmtTotalPaid);
        //case when paid amount is refunded and order has creditmemo created
        $creditmemos = ($this->getCreditmemosCollection() === false) ?
             true : (count($this->getCreditmemosCollection()) > 0);
        $paidAmtIsRefunded = $this->getTotalRefunded() == $totalPaid && $creditmemos;
        if (($hasDueAmount || $paidAmtIsRefunded) ||
            (!$checkAmtTotalPaid &&
            abs($totalRefunded - $this->getAdjustmentNegative()) < .0001)) {
            return false;
        }
        return true;
    }

    /**
     * Return increment id
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getIncrementId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $incrId = $this->getData('increment_id');
        $collection = $objectManager->create('Magento\Sales\Model\Order'); 
        $orderInfo = $collection->loadByIncrementId($incrId);
        $poNumber = $orderInfo->getPayment()->getPoNumber();
        $state =  $objectManager->get('Magento\Framework\App\State');
        $areaCode = $state->getAreaCode();
        if($areaCode == 'webapi_rest')
        {
            if($poNumber != '')
            {
               $incrId = $orderInfo->getPayment()->getPoNumber();
            }
            else
            {
                $incrId = $this->getData('increment_id'); 
            }
            return $incrId;
        }
        else
        {
            return $this->getData('increment_id');
        }   
    }

    /**
     * Is visible customer middlename
     *
     * @return bool
     */
    private function isVisibleCustomerMiddlename(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'customer/address/middlename_show',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if item is refunded.
     *
     * @param OrderItemInterface $item
     * @return bool
     */
    private function isRefunded(OrderItemInterface $item)
    {
        return $item->getQtyRefunded() == $item->getQtyOrdered();
    }

    /**
     * Is visible customer prefix
     *
     * @return bool
     */
    private function isVisibleCustomerPrefix(): bool
    {
        $prefixShowValue = $this->scopeConfig->getValue(
            'customer/address/prefix_show',
            ScopeInterface::SCOPE_STORE
        );

        return $prefixShowValue !== Nooptreq::VALUE_NO;
    }

    /**
     * Is visible customer suffix
     *
     * @return bool
     */
    private function isVisibleCustomerSuffix(): bool
    {
        $prefixShowValue = $this->scopeConfig->getValue(
            'customer/address/suffix_show',
            ScopeInterface::SCOPE_STORE
        );

        return $prefixShowValue !== Nooptreq::VALUE_NO;
    }

    //@codeCoverageIgnoreEnd
}
