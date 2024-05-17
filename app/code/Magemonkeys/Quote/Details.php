<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Cart\Quote;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\FormFactory as CustomerFormFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Details extends Template
{
    public const CUSTOMER_ACCOUNT_CREATE = 'customer_account_create';

    public const ADDITIONAL_ATTRIBUTES = [
        'prefix',
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'dob',
        'taxvat',
        'gender'
    ];

    /**
     * @var LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    public function __construct(
        ?CustomerFormFactory $customerFormFactory, // @deprecated
        Context $context,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        if (isset($this->jsLayout['components']['details']['config'])) {
            foreach ($this->layoutProcessors as $layoutProcessor) {
                $this->jsLayout = $layoutProcessor->process($this->jsLayout);
            }
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('\Magento\Customer\Model\Session');
        $customerId = $customerSession->getCustomerId();
        
        $att=array('select_sales_rep','facility');
        if ($customerId) {
            $customer = $objectManager->create('\Magento\Customer\Model\Customer')->load($customerId);
            $attributeValue = $customer->getData('sales_representative');
            /* key :- Customer,value :- Quote */
            $quote=array(32 => 13 ,28 => 14,29 => 15,35 => 16,27 => 17,40 => 19,41 => 18);
            
            $this->jsLayout['components']['details']['children']['quote-attributes-provider']['config']['data']['quote_entity']['select_sales_rep']=@$quote[$attributeValue];
            $quote_attribute_data=$this->jsLayout['components']['details']['children']['quote-attributes']['children'];
            
            foreach($att as $value){
                $result = array_filter($quote_attribute_data, function ($item) use ($value) {
                    return $item['dataScope'] === $value;
                });
                // echo $value;
            if (!empty($result)) {
                $parentKey = key($result);
                // $quote_attribute_data[$parentKey]['value']='2';
                
                $quote_attribute_data[$parentKey]['config']['disabled']=true;
                // print_r($quote_attribute_data[$parentKey]);
                // exit;
                $guest_customer_name=$quote_attribute_data[$parentKey]['dataScope'];
            }else {
                // $guest_customer_name='Guest';
            }
            // print_r($quote_attribute_data);
            }
            // exit;
            // echo $guest_customer_name;
        }
        // print_r()
        // $this->jsLayout['components']['details']['children']['quote-attributes']['children'][0]['config']['value']='test';
        // $this->jsLayout['components']['details']['children']['quote-attributes']['children'][0]['config']['disabled']=true;
        // exit;
        /* echo $value;
        
        $this->jsLayout['components']['details']['children']['customer-attributes']['children'][4]['visible']=0;
        //$this->jsLayout['components']['details']['children']['customer-attributes']['children'][3]['value']=true;


        /**/
        echo "<pre>";
        print_r($this->jsLayout['components']['details']['children']);
        exit; 
        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }
}
