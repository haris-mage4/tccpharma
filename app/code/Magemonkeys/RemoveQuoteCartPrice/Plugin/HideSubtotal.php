<?php
namespace Magemonkeys\RemoveQuoteCartPrice\Plugin;

class HideSubtotal
{

  protected $_customerSession;

  public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

  public function afterGetSectionData(\Amasty\RequestQuote\CustomerData\QuoteCart $subject, array $result)
    {
      $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
      if ($customerGroupId == 9 || $customerGroupId == 0) {
        $result['subtotal'] = '';
      }  
      return $result;
    }
}
