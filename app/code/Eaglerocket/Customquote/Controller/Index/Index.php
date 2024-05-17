<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eaglerocket\Customquote\Controller\Index;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Element\Template;




class Index extends Action
{
    private $dataPersistor;
    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */

    protected $context;
  //  private $fileUploaderFactory;
    private $fileSystem;


    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */

     public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Eaglerocket\Customquote\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    ) {
        parent::__construct($context);
        $this->_context = $context;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

    }

    public function execute()
    {
        $question = $this->getRequest()->getParam('question');
        $allQuestion = $this->helper->getQuestions();
        // echo "<pre>";
        // print_r($question);
        // print_r($allQuestion->getData());
        // die;
        $html = "<div class='all-question'>";
        $html .= "<div class='title'><h3 style='margin-left: 2px;padding-bottom: 20px;font-style: italic;color:#000;'> Please find Quote details here </h3></div>";
        foreach ($allQuestion as $row) {
            if(isset($question[$row->getPostId()])){
                 $html .= "<div class='question-block' style='font-size: 16px; border: 1px solid #ccc; margin-bottom: 15px;'>";
                 $html .= "<p style='padding: 18px 8px; border-bottom: 1px solid #ccc;color:#000;' class='question'> Question : <span style='font-size: 14px;'>".$row->getQuestion()."</span></p>";
                 $html .= "<p class='answer' style='font-weight: bold;padding: 10px;'> Answer : <span style='font-size: 14px;'>".$question[$row->getPostId()]."</span></p>";
                 $html .= "</div>";
             }
        }
        $html .= "</div>";
        $sender = [
            'name' => 'Walter Hoffman',
            'email' => 'walterhoffman@eaglerocketllc.com',
        ];
        $templateVars = array(
            'store' => $this->storeManager->getStore(),
            'customer_name' => 'John Doe',
            'message'   => $html
        );

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier('eaglerocket_custom_email_template') // this code we have mentioned in the email_templates.xml
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            // ->addTo($this->scopeConfig->getValue('contact/email/recipient_email', $storeScope))
            ->addTo('walterhoffman@eaglerocketllc.com')
            ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $this->messageManager->addSuccess(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->_redirect($this->_redirect->getRefererUrl());
            // $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            // $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return;
    }

}
