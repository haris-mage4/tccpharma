<?php

namespace Eagle\Tooltip\Plugin\Checkout\CustomerData;

use Magento\Customer\Model\Session;

class Cart
{
    protected $customerSession;

    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, array $result)
    {
        $customerId = $this->customerSession->getCustomerId();

        if ($customerId) {
            $customerGroupId = $this->customerSession->getCustomerGroupId();

            $result['customer_group_id'] = $customerGroupId;
        }

        return $result;
    }
}
