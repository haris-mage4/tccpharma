<?php

namespace Eagle\CustomSearchBlock\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Eagle\CustomSearchBlock\Helper\Data;

class Ajax extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
    }

    public function execute()
    {
        $inputValue = $this->getRequest()->getParam('inputValue');
        $result = $this->jsonFactory->create();

        if ($this->helper->isParamInSearch($inputValue)) {
            $result->setData(['status' => 'success', 'message' => true]);
        } elseif ($this->helper->isParamInSearchForMediacalPage($inputValue)) {
            $result->setData(['status' => 'error', 'message' => 'medical']);
        } else {
            $result->setData(['status' => 'error', 'message' => false]);
        }

        return $result;
    }
}
