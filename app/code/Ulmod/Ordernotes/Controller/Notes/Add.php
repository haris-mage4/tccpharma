<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Controller\Notes;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Ulmod\Ordernotes\Model\NotesFactory;

/**
 * Add notes controller
 */
class Add extends \Ulmod\Ordernotes\Controller\Notes
{
    /**
     * @var NotesFactory
     */
    protected $factory;

    /**
     * @var OrderLoaderInterface
     */
    protected $orderLoader;
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory,
        NotesFactory $factory
    ) {
        $this->orderLoader = $orderLoader;
        $this->resultPageFactory = $resultPageFactory;
        $this->factory = $factory;
        parent::__construct($context, $customerSession);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $customerId = $this->customerSession->getCustomer()->getId();
        $orderId = $this->getRequest()->getParam('order_id');
        $reply = $this->getRequest()->getParam('reply');
        $storeName = $this->getRequest()->getParam('store_name');
        $data = [
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'visible' => 1,
            'notify' => 1,
            'new_admin_note' => 0,
            'new_customer_note' => 1,
            'store_name' => $storeName,
            'note' => $reply
        ];

        $this->factory->create()
            ->addData($data)
            ->save();

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
