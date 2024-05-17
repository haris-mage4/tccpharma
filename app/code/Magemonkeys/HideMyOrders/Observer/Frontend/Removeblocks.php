<?php

namespace Magemonkeys\HideMyOrders\Observer\Frontend;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
 
class Removeblocks implements ObserverInterface
{
   protected $customerSession;
   public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }
   public function execute(\Magento\Framework\Event\Observer $observer)
   {
      $customerGroupId = $this->customerSession->getCustomerGroupId();
      if ($customerGroupId == 9 || $customerGroupId == 10 || $customerGroupId == 11 || $customerGroupId == 0) {
         $layout = $observer->getLayout();
         $layout->unsetElement('customer-account-navigation-orders-link');
      }      
   }
}