<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Block\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use Ulmod\Ordernotes\Model\NotesFactory;

/**
 * Email controller
 */
class Email
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     * @param NotesFactory $notesFactory
     * @param array $data
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        NotesFactory $notesFactory,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->notesFactory = $notesFactory;
    }

    /**
     * Get note
     *
     * @return mixed
     */
    public function getNote()
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        $note = $this->notesFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('id', 'desc')
            ->getFirstItem();
            
        return $note;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     */
    public function aroundToHtml(
        $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        $note = $this->getNote()->getNote();
        
        return $result . '<br /><b>' . __('Your Note')
            . '</b><br />' .  $note;
    }
}
