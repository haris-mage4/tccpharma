<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Controller;

use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
        
/**
 * Abstract notes controller
 */
abstract class Notes extends AppAction
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Check customer authentication for some actions
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set(
                '',
                'no-dispatch',
                true
            );
            $refererUrl = $this->_redirect->getRefererUrl();
            if (!$this->customerSession->getBeforeUrl()) {
                $this->customerSession->setBeforeUrl($refererUrl);
            }
        }
        
        return parent::dispatch($request);
    }
}
