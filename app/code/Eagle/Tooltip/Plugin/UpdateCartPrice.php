<?php

namespace Eagle\Tooltip\Plugin;

use Eagle\Tooltip\Helper\Config\TooltipConfig;
use Magento\Customer\Model\Session;

class UpdateCartPrice
{
    /**
     * @var TooltipConfig
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Plugin constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        TooltipConfig $helperData,
        Session $customerSession
    ) {
        $this->quote = $checkoutSession->getQuote();
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param $data
     * @return array
     */
    public function beforeUpdateItems(\Magento\Checkout\Model\Cart $subject, $data)
    {
        $quote = $subject->getQuote();

        foreach ($data as $key => $value) {
            $item = $quote->getItemById($key);
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $splcialpriceRange = $this->helperData->getTierPriceByQty($item->getSku(), $customerGroupId, $value['qty']);


            if (isset($splcialpriceRange)) {
                $price = $splcialpriceRange;

                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
            }
        }
    }
}
