<?php

namespace Magemonkeys\RepresentativeAttr\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderRepresentEmail implements ObserverInterface
{


     public function __construct(
        \Magemonkeys\RepresentativeAttr\Block\Widget\RepresentativeAttr $RepresentativeAttr
    )
    {
        $this->_instanceBlock = $RepresentativeAttr;
    }


    public function execute(Observer $observer)
    {
        $transport = $observer->getTransport();
        $order = $transport['order'];

            // $companyname = $order->getCompanyName();
            $representative = $this->_instanceBlock->getRepresentative();;

            // $transport['company_name'] = $companyname;
            $transport['representative'] = $representative;

    }
}