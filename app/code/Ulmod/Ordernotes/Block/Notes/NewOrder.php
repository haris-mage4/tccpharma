<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Block\Notes;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Ulmod\Ordernotes\Model\NotesFactory as NotesFactory;
    
/**
 * Notes from new order block
 */
class NewOrder extends Template
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
     * @var NotesFactory
     */
    protected $notesFactory;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @param array Context $context
     * @param array CustomerSession $customerSession
     * @param array CheckoutSession $checkoutSession
     * @param array NotesFactory $notesFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        NotesFactory $notesFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->notesFactory = $notesFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Ulmod\Ordernotes\Model\ResourceModel\Notes\Collection
     */
    public function getNote()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        $note = $this->notesFactory->create()->getCollection()
            ->addFieldToFilter('order_id', $lastOrderId)
            ->setOrder('id', 'desc')
            ->getFirstItem();
            
        return $note;
    }
}
