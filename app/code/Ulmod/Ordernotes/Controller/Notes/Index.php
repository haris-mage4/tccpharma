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
use Magento\Framework\Controller\ResultFactory;
        
/**
 * Notes index controller
 */
class Index extends \Ulmod\Ordernotes\Controller\Notes
{
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
        PageFactory $resultPageFactory
    ) {
        $this->orderLoader = $orderLoader;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $customerSession);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }

        $resultPage = $this->resultFactory
            ->create(ResultFactory::TYPE_PAGE);
        
        return $resultPage;
    }
}
