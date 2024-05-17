<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Observer;

use Ulmod\Ordernotes\Model\NotesFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AddOrderCommentToOrderNotes implements ObserverInterface
{
    /**
     * @var NotesFactory
     */
    protected $notesFactory;
    
    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param OrderInterface $orderInterface
     * @param NotesFactory $notesFactory
     * @param LoggerInterface $logger
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param BackendAuthSession $backendAuthSession
     */
    public function __construct(
        OrderInterface $orderInterface,
        NotesFactory $notesFactory,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderInterface = $orderInterface;
        $this->notesFactory = $notesFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderids = $observer->getEvent()->getOrderIds();
        
        foreach ($orderids as $orderid) {
            $order = $this->orderInterface->load($orderid);
                $umOrderComment = $order->getData('um_order_comment');
            if ($umOrderComment) {
                $this->notesFactory->create()
                    ->setNote($umOrderComment)
                    ->setOrderId($orderid)
                    ->setCustomerId($order->getCustomerId())
                    ->setStoreName($this->storeManager->getStore()->getName())
                    ->setVisible(1)
                    ->setNewCustomerNote(1)
                    ->setNewAdminNote(0)
                    ->save();
                   
                /* Send email to store owner */
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                $email = $this->scopeConfig
                    ->getValue('ulmod_ordernotes/notifications/admin/sender_email');
                            
                $emailName = $this->scopeConfig
                    ->getValue('ulmod_ordernotes/notifications/admin/sender_name');

                if ($email == '') {
                    $email = $this->scopeConfig->getValue(
                        'trans_email/ident_general/email',
                        $storeScope
                    );
                }

                if ($emailName == '') {
                    $emailName = $this->storeManager
                        ->getStore()->getName();
                }

                $transport = $this->transportBuilder
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getId(),
                        ]
                    )
                    ->setTemplateVars([
                        'note' => $umOrderComment,
                        'order' => $order
                    ])
                    ->setFrom([
                        'name' => $emailName,
                        'email' => $email
                    ]);

                // Email to admin if customer add new note
                    $to = $this->scopeConfig->getValue(
                        'ulmod_ordernotes/notifications/admin/admin_email',
                        $storeScope
                    );
                    
                    $templateId = $this->scopeConfig->getValue(
                        'ulmod_ordernotes/notifications/admin/template',
                        $storeScope
                    );

                $transport->setTemplateIdentifier($templateId);
                $transport->addTo($to);

                $transport = $transport->getTransport();
                $transport->sendMessage();

                $this->inlineTranslation->resume();
            }
        }
    }
}
