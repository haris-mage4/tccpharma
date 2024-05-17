<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Block\Notes;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Customer\Model\Session as CustomerSession;
use Ulmod\Ordernotes\Model\NotesFactory;

/**
 * Notes Index block
 */
class Index extends Template
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param DateTime $dateTime
     * @param CustomerSession $customerSession
     * @param NotesFactory $notesFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        CustomerSession $customerSession,
        NotesFactory $notesFactory,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->customerSession = $customerSession;
        $this->notesFactory = $notesFactory;
        parent::__construct($context, $data);
    }

    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $orderId =  $this->getRequest()->getParam('order_id');
        $notesCount = $this->notesFactory->create()
            ->getNotesCount($orderId)
            ? __('Order Notes %1', '<span class="counter">' . $notesCount . '</span>')
            : __('Order Notes');
        $this->setTitle($notesCount);
    }

    /**
     * @return \Ulmod\Ordernotes\Model\ResourceModel\Notes\Collection
     */
    public function getNotes()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $notesCollection = $this->notesFactory->create()
            ->getNotes($orderId);
        $notesCollection->addFieldToFilter('visible', 1);
        
        return $notesCollection;
    }

    /**
     * Get store
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return  $this->_storeManager->getStore();
    }
}
