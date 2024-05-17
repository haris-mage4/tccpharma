<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magemonkeys\Ordermail\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Helper\Data as PaymentHelper;
use \Magento\Customer\Model\Customer;
use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;

/**
 * Class OrderCommentSender
 */
class OrderCommentSender extends \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
{
    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Template $templateContainer
     * @param OrderCommentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        OrderCommentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        ManagerInterface $eventManager,
        Customer $customerModel,
        QuoteEntityRepositoryInterface $quoteEntityRepository
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer,$eventManager);
        $this->addressRenderer = $addressRenderer;
        $this->eventManager = $eventManager;
        $this->paymentHelper = $paymentHelper;
        $this->customerModel = $customerModel;
        $this->quoteEntityRepository = $quoteEntityRepository;



    }

    /**
     * Send email to customer
     *
     * @param Order $order
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Order $order, $notify = true, $comment = '')
    {
        $this->identityContainer->setStore($order->getStore());
        $createdAt = $order->getCreatedAt();
        $formattedDate = date("M d, Y, h:i:s A", strtotime($createdAt));
        $timezoneInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $currentDate = $timezoneInterface->date(new \DateTime($formattedDate));
        $formattedDate = $timezoneInterface->formatDateTime($currentDate, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT);
        $attribute_html = '';
        $facility_name = '';

        try{
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customer_id=$order->getCustomerId();
                $quote= $objectManager->create(\Amasty\RequestQuote\Model\Quote::class)->getCollection()->addFieldToFilter('customer_id',['eq' => $customer_id])
                ->addFieldToFilter('status',['eq' => 1])->getLastItem();
                $quoteId = $quote->getId();
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
                        }
                    }
                    if($attributeCode == 'facility'){
                        $attributeLabel = $attribute->getDefaultFrontendLabel();
                        $facility_name = $attributeLabel.' : '.$attributeValue;
                    }
            }
            
           /*  $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/u.log');
            $zendLogger = new \Zend_Log();
            $zendLogger->addWriter($writer);
            $zendLogger->info($attribute_html); */

        }catch(\Exception $e){
           /*  $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/u.log');
            $zendLogger = new \Zend_Log();
            $zendLogger->addWriter($writer);
            $zendLogger->info($e->getMessage()); */
        }
            
        $transport = [
            'order' => $order,
            'comment' => $comment,
            'payment_html' => $this->getPaymentHtml($order),
            'created_at'=>$formattedDate,
            'submit_fields'=>$attribute_html,
            'facility_name' => $facility_name,
            'billing' => $order->getBillingAddress(),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'order_data' => [
                'customer_name' => $order->getCustomerName(),
                'frontend_status_label' => $order->getFrontendStatusLabel()
            ]
        ];

        $transportObject = new DataObject($transport);

        /**
         * Event argument `transport` is @deprecated. Use `transportObject` instead.
         */
        $this->eventManager->dispatch(
            'email_order_comment_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transportObject->getData());

        return $this->checkAndSend($order, $notify);
    }
      protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
}
