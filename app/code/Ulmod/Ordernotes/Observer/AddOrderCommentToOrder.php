<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddOrderCommentToOrder implements ObserverInterface
{

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $order \Magento\Sales\Model\Order * */
        $order = $observer->getEvent()->getOrder();
        
        /** @var $quote \Magento\Quote\Model\Quote * */
        $quote = $observer->getEvent()->getQuote();

        $order->setData('um_order_comment', $quote->getUmOrderComment());
    }
}
