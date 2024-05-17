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
use Magento\Customer\Model\Session;
use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;
use Amasty\QuoteAttributes\Model\QuoteEntity;
use Amasty\RequestQuote\Model\Quote;
use Magento\Customer\Model\CustomerFactory;
use Magento\Eav\Model\Config as EavConfig;


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
        Session $customerSession,
        QuoteEntityRepositoryInterface $quoteEntityRepository,
        QuoteEntity $quoteEntity,
        Quote $quote,
        CustomerFactory $customerFactory,
        EavConfig $eavConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
        $this->customerSession = $customerSession;
        $this->quoteEntityRepository = $quoteEntityRepository;
        $this->quoteEntity = $quoteEntity;
        $this->quote = $quote;
        $this->customerFactory = $customerFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {

        $attribute1 = $this->eavConfig->getAttribute('customer', 'sales_representative');
        $attribute2 = $this->eavConfig->getAttribute('amasty_quote', 'select_sales_rep');
        if(($attribute1 && $attribute1->usesSource()) && ($attribute2 && $attribute2->usesSource())) {
            /** @var AbstractSource $sourceModel */
            $sourceModel = $attribute1->getSource();
            $options1 = $sourceModel->getAllOptions();
             $sourceModel = $attribute2->getSource();
            $options2 = $sourceModel->getAllOptions();
            $attr_result = [];
        
        for ($i = 1; $i < count($options1); $i++) {
            $attr_result[$options1[$i]['value']] = $options2[$i]['value'];
        }
        }







        if (isset($this->jsLayout['components']['details']['config'])) {
            foreach ($this->layoutProcessors as $layoutProcessor) {
                $this->jsLayout = $layoutProcessor->process($this->jsLayout);
            }
        }
        $att=array('select_sales_rep','facility');
        $att_customer=array('facility_name','sales_representative');
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $quote= $this->quote->getCollection()->addFieldToFilter('customer_id',['eq' => $customerId])->
            addFieldToFilter('status',['eq' => 1])->getLastItem();
            $quoteId = $quote->getId();            
            // if($quoteId){
            //     $attribute_html = '';
            //     $quoteEntityRepository = $this->quoteEntityRepository->getByQuoteId($quoteId);
            //     $model=$this->quoteEntity->load($quoteEntityRepository->getEntityId());
            //     $quote_attribute_data = $model->getData(); 
            //     $this->jsLayout['components']['details']['children']['quote-attributes-provider']['config']['data']['quote_entity']['select_sales_rep']
            //     =$quote_attribute_data['select_sales_rep'];
            //     $quoteData=$this->jsLayout['components']['details']['children']['quote-attributes']['children'];
            //     foreach($att as $value){
            //         $result = array_filter($quoteData, function ($item) use ($value) {
            //             return $item['dataScope'] === $value;
            //         });
            //         if (!empty($result)) {
            //             $parentKey = key($result);
            //             $this->jsLayout['components']['details']['children']['quote-attributes']['children'][$parentKey]['config']['disabled']=true;
            //             $this->jsLayout['components']['details']['children']['quote-attributes']['children'][$parentKey]['config']['value']
            //             =   ;
            //         }
            //     }
            // }else{
                if ($customerId) {
                    $customer = $this->customerFactory->create()->load($customerId);
                    $attributeValue = $customer->getData('sales_representative');
                    $quote=array(32 => 13 ,28 => 14,29 => 15,35 => 16,27 => 17,40 => 19,41 => 18);
                    $quoteData=$this->jsLayout['components']['details']['children']['quote-attributes']['children'];
                    foreach($att as $value){
                        $result = array_filter($quoteData, function ($item) use ($value) {
                            return $item['dataScope'] === $value;
                        });
                        if (!empty($result)) {
                            $parentKey = key($result);
                            // print_r($result);
                            if($result[$parentKey]['dataScope'] == 'facility'){
                                $this->jsLayout['components']['details']['children']['quote-attributes']['children'][$parentKey]['config']['disabled']=true;
                                $this->jsLayout['components']['details']['children']['quote-attributes']['children'][$parentKey]['config']['value']
                                =$customer->getData('facility_name');
                            }
                            if($result[$parentKey]['dataScope'] == 'select_sales_rep'){
                                $this->jsLayout['components']['details']['children']['quote-attributes']['children'][$parentKey]['config']['disabled']=true;
                                $this->jsLayout['components']['details']['children']['quote-attributes']['children'][$parentKey]['config']['value']
                                =$attr_result[$customer->getData('sales_representative')];
                            }
                        }
                    }
                    $this->jsLayout['components']['details']['children']['quote-attributes-provider']['config']['data']['quote_entity']
                    ['select_sales_rep']=$attr_result[$customer->getData('sales_representative')];
                }
            // }
        }else{
            $quoteData=$this->jsLayout['components']['details']['children']['customer-attributes']['children'];
            foreach($att_customer as $value){
                $result = array_filter($quoteData, function ($item) use ($value) {
                    return $item['dataScope'] === $value;
                });
                if (!empty($result)) {
                    $parentKey = key($result);
                    $this->jsLayout['components']['details']['children']['customer-attributes']['children'][$parentKey]['visible']=0;
                }
            }
            
        }
        // echo "<pre>";
        // print_r($this->jsLayout);
        // exit;

        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }
    
}
