<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Model;

use Magento\Framework\Event\ObserverInterface;
use Ulmod\Ordernotes\Model\NotesFactory;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Checkout model
 */
class Checkout implements ObserverInterface
{
    /**
     * @var NotesFactory
     */
    protected $factory;

    /**
     * @param NotesFactory $factory
     */
    public function __construct(
        NotesFactory $factory
    ) {
        $this->factory = $factory;
    }
    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getOrder();

        /* @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();

        $orderNote = $quote->getPayment()
            ->getData('order_note');
            
        $customerId = $order->getCustomerId();
        $orderId =  $order->getOrderId();
        
        $data = [
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'note' => $orderNote
        ];

        $this->factory->create()
            ->setNote($data['note'])
            ->setOrderId($orderId)
            ->setCustomerId($customerId)
            ->setVisible(1)
            ->save();

        return $this;
    }
}
