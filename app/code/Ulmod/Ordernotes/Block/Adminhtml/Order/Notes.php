<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Ulmod\Ordernotes\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Ulmod\Ordernotes\Model\NotesFactory;
use Magento\Sales\Model\OrderFactory;
       
class Notes extends Template
{

    /**
     * @var NotesFactory
     */
    protected $notesFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;
    
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @param Context $context,
     * @param Registry $registry,
     * @param NotesFactory $notesFactory,
     * @param OrderFactory $orderFactory,
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        NotesFactory $notesFactory,
        OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->notesFactory = $notesFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function resolveCurrentWebsiteId()
    {
        if ($this->_appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            // in admin area
            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $this->_request;
            $storeId = (int) $request->getParam('store', 0);
        } else {
            // frontend area
            $storeId = true; // get current store from the store resolver
        }

        $store = $this->_storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        return $websiteId;
    }
    
    /**
     * @return int
     */
    public function getCurrectAdminStoreName()
    {
        if ($this->_appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            // in admin area
            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $this->_request;
            $storeId = (int) $request->getParam('store', 0);
        } else {
            // frontend area
            $storeId = true; // get current store from the store resolver
        }

        $store = $this->_storeManager->getStore($storeId);
        $currectStoreName = $store->getName();

        return $currectStoreName;
    }

    /**
     * @return bool
     */
    public function getIsOrder()
    {
        return false;
    }
    
    /**
     * @return void
     */
    public function getCollection()
    {
        $notesCollection = $this->notesFactory
            ->create()
            ->getCollection()
            ->setOrder('updated_at', 'asc');

        $notesCollection->addFieldToFilter(
            'order_id',
            $this->getOrderId()
        );

        $notesCollection->getSelect()->joinLeft(
            ['user'=>$notesCollection->getTable('admin_user')],
            'user.user_id=main_table.user_id',
            ['firstname', 'lastname']
        );

        return $notesCollection;
    }

    /**
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('id');
    }
}
