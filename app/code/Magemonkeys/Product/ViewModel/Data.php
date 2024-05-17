<?php

namespace Magemonkeys\Product\ViewModel;
use     \Magento\Framework\App\Config\ScopeConfigInterface;
class Data implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Catalog\Model\CustomerFactory     $customerFactory
     */
    public function __construct(
    ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig=$scopeConfig;
    }

    public function getProductQtyStatus(){
        $status=$this->scopeConfig->getValue('hideqty/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $status;
    }
    public function getModuleStatus(){
        $status=$this->scopeConfig->getValue('hideqty/general/module_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $status;
    }
}