<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Ulmod\Ordernotes\Model;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Ulmod\Ordernotes\Model\NotesFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Model\Auth\Session as BackendAuthSession;
use Magento\Framework\Event\Observer as EventObserver;
    
class Observer implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var NotesFactory
     */
    protected $factory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var BackendAuthSession
     */
    protected $backendAuthSession;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param NotesFactory $factory
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param OrderFactory $orderFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param BackendAuthSession $backendAuthSession
     */
    public function __construct(
        LoggerInterface $logger,
        NotesFactory $factory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfig,
        BackendAuthSession $backendAuthSession
    ) {
        $this->logger = $logger;
        $this->factory = $factory;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->backendAuthSession = $backendAuthSession;
        $this->scopeConfig = $scopeConfig;
        $this->orderFactory = $orderFactory;
    }
    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $noteData = $observer->getEvent()->getDataObject();
        if (isset($noteData['notify']) && $noteData['notify'] == 1) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->orderFactory->create()
                ->load($noteData->getOrderId());

            /* Send email to store owner */
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            
            $storeId =  $this->storeManager->getStore()->getId();
            
            $emailName = $this->scopeConfig
                ->getValue('ulmod_ordernotes/notifications/customer/sender_name');
            if ($emailName == '') {
                $emailName = $this->storeManager
                    ->getStore()->getName();
            }
            
            $email = $this->scopeConfig
                ->getValue('ulmod_ordernotes/notifications/customer/sender_email');
            if ($email == '') {
                $email = $this->scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    $scope
                );
            }
            
            $transport = $this->transportBuilder
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars([
                    'note' => $noteData,
                    'order' => $order
                ])
                ->setFrom([
                    'name' => $emailName,
                    'email' => $email
                ]);

           // Email to customer if admin add new note
            $receiver = $order->getCustomerEmail();
            $templateIdConfig = $this->scopeConfig->getValue(
                'ulmod_ordernotes/notifications/customer/template',
                $scope
            );

            // Email to admin if customer add new note
            if (isset($noteData['customer_id'])) {
                $email = $this->scopeConfig
                    ->getValue('ulmod_ordernotes/notifications/admin/sender_email');
                    
                $emailName = $this->scopeConfig
                    ->getValue('ulmod_ordernotes/notifications/admin/sender_name');
                
                $receiver = $this->scopeConfig->getValue(
                    'ulmod_ordernotes/notifications/admin/admin_email',
                    $scope
                );
                
                $templateIdConfig = $this->scopeConfig->getValue(
                    'ulmod_ordernotes/notifications/admin/template',
                    $scope
                );
            }

            $transport->setTemplateIdentifier($templateIdConfig);
            $transport->addTo($receiver);

            $transport = $transport->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }
        
        return $this;
    }
}
