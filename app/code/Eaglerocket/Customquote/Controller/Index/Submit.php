<?php

namespace Eaglerocket\Customquote\Controller\Index;

// use Zend\Log\Filter\Timestamp;
// use Magento\Store\Model\StoreManagerInterface;


use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Eaglerocket\Customquote\Model\ExtensionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

class Submit extends Action
{
    // const XML_PATH_EMAIL_RECIPIENT_NAME = 'trans_email/ident_support/name';
    // const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'trans_email/ident_support/email';
     
    // protected $_inlineTranslation;
    // protected $_transportBuilder;
    // protected $_scopeConfig;
    // protected $_logLoggerInterface;
    // protected $storeManager;


    protected $resultPageFactory;
    protected $extensionFactory;

     public function __construct(  //      \Magento\Framework\App\Action\Context $context,
    //     \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
    //     \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
    //     \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    //     \Psr\Log\LoggerInterface $loggerInterface,
    //     StoreManagerInterface $storeManager
    //     array $data = [],

        Context $context,
        PageFactory $resultPageFactory,
        ExtensionFactory $extensionFactory
    )
     {//$this->_inlineTranslation = $inlineTranslation;
    //     $this->_transportBuilder = $transportBuilder;
    //     $this->_scopeConfig = $scopeConfig;
    //     $this->_logLoggerInterface = $loggerInterface;
    //     $this->messageManager = $context->getMessageManager();
    //     $this->storeManager = $storeManager;



        $this->resultPageFactory = $resultPageFactory;
        $this->extensionFactory = $extensionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {            

            $data = (array)$this->getRequest()->getPost();
            if ($data) {
                $model = $this->extensionFactory->create();
                $model->setData($data)->save();

// Send Mail
            // $this->_inlineTranslation->suspend();
                         
            // $sender = [
            //     'name' => "kirti",
            //     'email' => "kirti003shukla@gmail.com"
            // ];
             
            // $sentToEmail = $this->_scopeConfig ->getValue('trans_email/ident_general/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
             
            // $sentToName = $this->_scopeConfig ->getValue('trans_email/ident_general/name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
             
             
            // $transport = $this->_transportBuilder
            // ->setTemplateIdentifier('customemail_email_template')
            // ->setTemplateOptions(
            //     [
            //         'area' => 'frontend',
            //         'store' => $this->storeManager->getStore()->getId()
            //     ]
            //     )
            //     ->setTemplateVars([
            //         'name'  => "kirti",
            //         'email'  => "kirti003shukla@gmail.com"
            //     ])
            //     ->setFromByScope($sender)
            //     ->addTo("kirti.bhumca2015@gmail.com","kirti")
            //     //->addTo('owner@example.com','owner')
            //     ->getTransport();
                 
            //     $transport->sendMessage();
                 
            //     $this->_inlineTranslation->resume();
            //     $this->messageManager->addSuccess('Email sent successfully');


                $this->messageManager->addSuccessMessage(__("Data Saved Successfully."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e, __("We can\'t submit your request, Please try again."));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;

    }
}