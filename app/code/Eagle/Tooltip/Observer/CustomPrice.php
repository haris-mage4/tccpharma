<?php

namespace Eagle\Tooltip\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Eagle\Tooltip\Helper\Config\TooltipConfig;

class CustomPrice implements ObserverInterface
{
    protected $customerSession;
    protected $groupRepository;
    protected $priceCurrency;
    protected $tooltipConfig;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        TooltipConfig $tooltipConfig
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->priceCurrency = $priceCurrency;
        $this->tooltipConfig = $tooltipConfig;
    }

    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $product = $observer->getEvent()->getData('product');
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        if ($this->tooltipConfig->tierPriceByCustomerId($product->getSku(), $customerGroupId) !== null) {
            // $customPrice = $this->tooltipConfig->tierPriceByCustomerId($product->getSku(), $customerGroupId);
            // $specilaQty = $this->tooltipConfig->getSpecialPriceQtyByCustomerGroup($product->getSku(), $customerGroupId);
            // if ($specilaQty !== null) {
            //     $specilaQty = rtrim(rtrim($specilaQty, '0'), '.');
            // }
        
             $splcialpriceRange = $this->tooltipConfig->getTierPriceByQty($product->getSku(), $customerGroupId, $item->getQty());
            // if ($specilaQty !== null && $specilaQty >= $qty) {
                $item->setCustomPrice($splcialpriceRange);
                $item->setOriginalCustomPrice($splcialpriceRange);
                $item->getProduct()->setIsSuperMode(true);
            // }
        }
    }
}
