<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Block\Order;

use Magento\Sales\Block\Order\History as OrderHistory;
use Ulmod\Ordernotes\Model\NotesFactory as NotesFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Helper\Reorder as SalesReorder;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Ulmod\Ordernotes\Api\FiltersInterface;

class History extends OrderHistory
{
    /**
     * @var NotesFactory
     */
    protected $notesFactory;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Config
     */
    protected $_orderConfig;

    /**
     * @var SalesReorder
     */
    protected $salesReorder;
    
    /**
     * @var PostHelper
     */
    protected $postHelper;

    protected $_storeManager;
    protected $_blockFactory;
    protected $_escape;
    protected $orderCollectionFactory;
    /**
     * @var FiltersInterface[]
     */
    private $specialFilter;
    
    /**
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     * @param NotesFactory $notesFactory
     * @param SalesReorder $salesReorder
     * @param PostHelper $postHelper
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param Escaper $escape
     * @param FiltersInterface[] $specialFilter
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig,
        NotesFactory $notesFactory,
        SalesReorder $salesReorder,
        PostHelper $postHelper,
        StoreManagerInterface             $storeManager,
        BlockFactory                      $blockFactory,
        Escaper                           $escape,
        array                             $specialFilter = [],
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->notesFactory = $notesFactory;
        $this->salesReorder = $salesReorder;
        $this->postHelper = $postHelper;
         $this->_storeManager = $storeManager;
        $this->_blockFactory = $blockFactory;
        $this->_escape = $escape;
        $this->specialFilter = $specialFilter;
        $this->validate();
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
    }

     /**
      * @return PostHelper
      */
    public function getPostHelper()
    {
        return $this->postHelper;
    }
    
     /**
      * @return SalesReorder
      */
    public function getSalesReorder()
    {
        return $this->salesReorder;
    }
    
     /**
      * @return void
      */
    public function getNotesCollection()
    {
        return $this->notesFactory->create();
    }

   /**
    * @param $_order
    * @return array
    */
    public function getLastNoteByOrder($_order)
    {
        $lastNoteByOrder = $this->getNotesCollection()->getCollection()
            ->addFieldToFilter('order_id', $_order->getEntityId())
            ->setOrder('id', 'asc')
            ->getLastItem();
            
        return $lastNoteByOrder;
    }

   /**
    * @param $_order
    * @return array
    */
    public function getOrderNotesReadUrl($_order)
    {
        $orderNotesRead = $this->getUrl('ordernotes/notes/read') ;
        $orderNotesReadUrl = $orderNotesRead . 'order_id/' .$_order->getEntityId();
            
        return $orderNotesReadUrl;
    }
    
   /**
    * @param $lastNoteByOrder
    * @return array
    */
    public function isNewMessage($lastNoteByOrder)
    {
        if ($lastNoteByOrder->getNewAdminNote() == 1
            && $lastNoteByOrder->getNewCustomerNoteMarkread() != 1
            && $lastNoteByOrder->getVisible() == 1
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return CollectionFactory
     */
    private function getOrderCollectionFactory(): CollectionFactory
    {
        return $this->orderCollectionFactory;
    }

    /*Order Filters*/

    /**
     * @return Collection
     */
    public function getOrderList(): Collection
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            return $this->getOrderActive($customerId);
        } else {
            return $this->getOrderFilterList($customerId);
        }
        return false;
    }

    /**
     * @param $customerId
     * @return Collection
     */
    private function getOrderActive($customerId): Collection
    {
        $this->orders = $this->getOrderCollectionFactory()
            ->create($customerId)
            ->addFieldToSelect('*');
        $this->orders->addFieldToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
        )->setOrder(
            'created_at',
            'desc'
        );

        return $this->orders;
    }

    /**
     * @param $customerId
     * @return Collection
     */
    private function getOrderFilterList($customerId): Collection
    {
        $post = $this->getRequest()->getParams();
        if (isset($post)) {
            $this->orders = $this->getOrderCollectionFactory()
                ->create($customerId);
            if (!empty($post['order_id'])) {
                $this->orders->addFieldToFilter(
                    'increment_id',                    
                    array('like' => '%'.$post['order_id'].'%')
                );
            }

            if (!empty($post['grand_total'])) {
                $this->orders->addFieldToFilter(
                    'grand_total',
                    $post['grand_total']
                );
            }

            $this->orders->addFieldToSelect('*');

            foreach ($this->specialFilter as $filters) {
                if ($filters->isFilterable($post)) {
                    $this->orders = $filters->filter($this->orders, $post);
                }
            }

            $this->orders->addFieldToFilter(
                'status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        return $this->orders;
    }

    /*Order Filters*/

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrderList()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sales.order.history.pagersearch'
            )->setCollection(
                $this->getOrderList()
            );
            $this->setChild('pager', $pager);
            $this->getOrderList()->load();
        }
        return $this;
    }

    /*Order Filters*/

    /**
     * @param $order
     * @return string|void
     */
    public function getProductName($order)
    {
        /* $items = $order->getAllItems();
        if ($items) {
            $total_qty = [];
            foreach ($items as $itemId => $_item) {
                $total_qty[][$itemId] = $_item->getName();
            }
            $count = 0;
            $html = "<p>";
            foreach ($total_qty as $item) {
                $html .= ($count + 1) . ' ) ' . $item[$count] . ' <br/>';
                $count++;
            }
            $html .= "</p>";
            return $html;
        } */
        $items = $order->getAllItems();
        if ($items) {
            $total_qty = [];
            $counter=0;
            foreach ($items as $itemId => $_item) {
                $total_qty[][$itemId] = $_item->getName();
                $product = $_item->getProduct();
                $productUrl = '';
                if ($product) {
                    $productUrl = $product->getProductUrl();
                }
                $total_qty[$counter]['url'] = $productUrl;
                $counter++;
            }
            $count = (int)0;
            $html = "<p>";
            foreach ($total_qty as $item) {
                $html .= '<a href="'.$item['url'].'">'.($count + 1) . ' ) ' . $item[$count] . '</a> <br/>';
                $count++;
            }
            $html .= "</p>";
            return $html;
        }

    }

    /**
     * @param $order
     * @return string|void
     */
    public function getProductSku($order)
    {
        $items = $order->getAllItems();
        if ($items) {
            $total_qty = [];
            foreach ($items as $itemId => $item) {
                $total_qty[][$itemId] = $item->getSku();
            }
            $count = 0;
            $html = "<p>";
            foreach ($total_qty as $item) {
                $html .= ($count + 1) . ' ) ' . $item[$count] . ' <br/>';
                $count++;
            }
            $html .= "</p>";
            return $html;
        }
    }

    /**
     * @return void
     */
    private function validate(): void
    {
        foreach ($this->specialFilter as $specialFilter) {
            if (!$specialFilter instanceof FiltersInterface) {
                throw new InvalidArgumentException('Invalid object type.');
            }
        }
    }

    /*Order Filters*/
}
