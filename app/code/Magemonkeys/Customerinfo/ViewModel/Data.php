<?php

namespace Magemonkeys\Customerinfo\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Data implements ArgumentInterface
{
    /**
     * @var Session
     */
    private Session $customerSession;
    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @param Session $customerSession
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Session $customerSession,
        CustomerFactory $customerFactory

    ) {
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @return false|string
     */
    public function getData()
    {
        try{
            $attribute_html='';
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getCustomer()->getId();
                $customerModel = $this->customerFactory->create()->load($customerId);
                $saleRepValue = $customerModel->getData('sales_representative');
                $facilityValue = $customerModel->getData('facility_name');
                $attributeFacility=$customerModel->getResource()->getAttribute('facility_name');
                $attributeSaleRep=$customerModel->getResource()->getAttribute('sales_representative');
                $optionLabel = $attributeSaleRep->getSource()->getOptionText($saleRepValue);
                $attribute_html .= "<span>" . $attributeSaleRep->getStoreLabel() . "</span> : " .$optionLabel. "<br>";;
                $attribute_html .= "<span>" . $attributeFacility->getStoreLabel() . "</span> : " .$facilityValue;
                return $attribute_html;
            }else{
                return false;
            }

        }catch(\Exception $e){
            return false;
        }
    }
}
