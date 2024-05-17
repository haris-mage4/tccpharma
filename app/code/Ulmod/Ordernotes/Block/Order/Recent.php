<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Block\Order;

use Magento\Sales\Block\Order\Recent as OrderRecent;
use Ulmod\Ordernotes\Model\NotesFactory as NotesFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Helper\Reorder as SalesReorder;
use Magento\Framework\Data\Helper\PostHelper;

class Recent extends OrderRecent
{
 /**
  * @var string
  */
    protected $_template = 'order/recent.phtml';

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
    
    /**
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     * @param NotesFactory $notesFactory
     * @param SalesReorder $salesReorder
     * @param PostHelper $postHelper
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
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->notesFactory = $notesFactory;
        $this->salesReorder = $salesReorder;
        $this->postHelper = $postHelper;
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
}
