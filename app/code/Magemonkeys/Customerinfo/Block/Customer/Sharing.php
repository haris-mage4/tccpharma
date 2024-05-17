<?php
namespace Magemonkeys\Customerinfo\Block\Customer;

class Sharing extends \Magento\Wishlist\Block\Customer\Sharing
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pageTitle = __('Favorites Sharing');
        $this->pageConfig->getTitle()->set($pageTitle);

        return $this;
    }
}
