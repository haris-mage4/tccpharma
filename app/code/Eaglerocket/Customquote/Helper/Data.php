<?php

namespace Eaglerocket\Customquote\Helper;

use Magento\Framework\App\Helper\Context;
use Eaglerocket\Customquote\Model\Post;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
    
    protected $stockAvailability;
    public function __construct(
        Post $collectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Context $context
    )
    {
        parent::__construct($context);
        $this->collection = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function getQuestions()
    {
        $collation = $this->collection->getCollection();
        return $collation;
    }

    public function getAdminEmail(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);
    }

}