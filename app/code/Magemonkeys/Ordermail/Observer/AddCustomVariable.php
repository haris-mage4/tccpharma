<?php
namespace Magemonkeys\Ordermail\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session;
use \Magento\Customer\Model\Customer;
use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;

class AddCustomVariable implements ObserverInterface
{
    protected $customerSession;

    public function __construct(Session $customerSession,Customer $customerModel,QuoteEntityRepositoryInterface $quoteEntityRepository)
    {
        $this->customerSession = $customerSession;
        $this->customerModel = $customerModel;
        $this->quoteEntityRepository = $quoteEntityRepository;

    }


    public function execute(Observer $observer)
    {
        $attribute_html = '';
        $facility_name = '';
         try{
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $transport = $observer->getData('transport');
            $order = $transport['order'];
            $customer_id=$order->getCustomerId();
            $quote= $objectManager->create(\Amasty\RequestQuote\Model\Quote::class)->getCollection()
            ->addFieldToFilter('customer_id',['eq' => $customer_id])
            ->addFieldToFilter('status',['eq' => 1])->getLastItem();
            $quoteId = $quote->getId();
            $attribute_html = '';
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $quoteEntityRepository = $this->quoteEntityRepository->getByQuoteId($quoteId);
            $model=$objectManager->create(\Amasty\QuoteAttributes\Model\QuoteEntity::class)->load($quoteEntityRepository->getEntityId());
            $quote_attribute_data = $model->getData();
            unset($quote_attribute_data['entity_id']);
            unset($quote_attribute_data['quote_id']);
            foreach($quote_attribute_data as $key => $value){
                $attributeCode = $key; // Replace with the actual attribute code
                $attributeValue = $value;
                $attributeOptions = $model->getResource()->getAttribute($attributeCode)->getSource()->getAllOptions();
                $attribute = $model->getResource()->getAttribute($attributeCode);
                $attributeLabel = $attribute->getDefaultFrontendLabel();
                if($attributeCode == 'select_sales_rep'){
                    foreach ($attributeOptions as $option) {
                        if ($option['value'] == $attributeValue) {
                            $attribute_html .= "<span><b>" . $attribute->getDefaultFrontendLabel() . "</b></span> : " .$option['label'] . "<br>";
                            $sales_rep_for_all_quote = "<span><b>" . $attribute->getDefaultFrontendLabel() . "</b></span> : " .$option['label']. "<br>";
                            break;
                        }
                    }
                }elseif($attributeCode == 'target_price'){
                    if($attributeValue == false){
                        $attribute_html .= "<br><span><b>" . $attributeLabel . "</b></span> : No <br>";
                    }else{
                        $attribute_html .= "<br><span><b>" . $attributeLabel . "</b></span> : Yes <br>";
                    }
                }else{
                    if ($attribute) {
                        $attributeLabel = $attribute->getDefaultFrontendLabel();
                        $attribute_html .= "<br><span><b>" . $attributeLabel . "</b></span> : " . $attributeValue . "<br>";
                    } else {
                        // echo 'Attribute not found.';
                    }
                }
                if($attributeCode == 'facility'){
                    $attributeLabel = $attribute->getDefaultFrontendLabel();
                    $facility_name = $attributeLabel.' : '.$attributeValue;
                }
            }
            $transport['submit_fields'] = $attribute_html;
            $transport['facility_name'] = $facility_name;

            $observer->setData('transport', $transport);
           /*  $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/u.log');
            $zendLogger = new \Zend_Log();
            $zendLogger->addWriter($writer);
            $zendLogger->info($attribute_html); */

        }catch(\Exception $e){
            /* $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/u.log');
            $zendLogger = new \Zend_Log();
            $zendLogger->addWriter($writer);
            $zendLogger->info($e->getMessage()); */
        }
        // exit;
    }
}
