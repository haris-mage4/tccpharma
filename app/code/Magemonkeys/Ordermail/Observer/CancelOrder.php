<?php
namespace Magemonkeys\Ordermail\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;

class CancelOrder implements ObserverInterface
{
    protected $transportBuilder;
    private $storeManager;

    public function __construct(
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        QuoteEntityRepositoryInterface $quoteEntityRepository

    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->quoteEntityRepository = $quoteEntityRepository;

    }

    public function execute(Observer $observer)
    {
        /*$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $zendLogger = new \Zend_Log();
        $zendLogger->addWriter($writer);*/
        $facility_name = '';
        $attribute_html = '';
        try{
           
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $observer->getEvent()->getOrder();
            $customer_id=$order->getCustomerId();
            $quote= $objectManager->create(\Amasty\RequestQuote\Model\Quote::class)->getCollection()->addFieldToFilter('customer_id',['eq' => $customer_id])->
            addFieldToFilter('status',['eq' => 1])->getLastItem();
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
                $optionLabel = '';
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
                        $attribute_html .= "<br><span><b>" . $attributeLabel . "</b></span> : " . $value . "<br>";
                    } else {
                        // echo 'Attribute not found.';
                    }
                }
                if($attributeCode == 'facility'){
                    $attributeLabel = $attribute->getDefaultFrontendLabel();
                    $facility_name = $attributeLabel.' : '.$attributeValue;
                }
            }
            $transport['sales_rep'] = $attribute_html;
            $transport['facility_name'] = $facility_name;
            // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cancel_order.log');
            // $zendLogger = new \Zend_Log();
            // $zendLogger->addWriter($writer);
            // $zendLogger->info($facility_name);
            $observer->setData('transport', $transport);

        }catch(\Exception $e){
            // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/u.log');
            // $zendLogger = new \Zend_Log();
            // $zendLogger->addWriter($writer);
            // $zendLogger->info($e->getMessage()  );
        }

        $store = $this->storeManager->getStore();

        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        //$zendLogger->info('order id'.$orderId);
         $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $store->getId()
        ];
        $templateVars = [
            'order_id' => $orderId,
            'increment_id' => $order->getIncrementId(),
            'customer_name' => $order->getCustomerName(),
            'submit_fields' => $attribute_html,
            'facility_name' => $facility_name
        ];
       /* $from = ['email' => $order->getCustomerEmail(),
                 'name' => $order->getCustomerName()];*/
        $to = ['email' => $order->getCustomerEmail(),
               'name' => $order->getCustomerName()];
        $templateId = '18';
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom('sales')
            ->addTo($to['email'], $to['name'])
            ->getTransport();
        $transport->sendMessage();

        // Your code to handle cancelled order goes here
    }
}
