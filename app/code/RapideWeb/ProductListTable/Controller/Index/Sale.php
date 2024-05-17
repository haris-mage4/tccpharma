<?php


namespace RapideWeb\ProductListTable\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use RapideWeb\ProductListTable\Helper\Data;

/**
 * @company RapideWeb
 * @package RapideWeb_ProductListTable
 * @author James Wu <james4u.boda@gmail.com>
 * @date Mar 30, 2018
 *
 */

class Sale extends Action
{

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * HTTP Request
     *
     * @var Http
     */
    protected Http $request;

    /**
     * RapideWeb ProductListTable Helper Data
     *
     * @var Data
     */
    protected Data $helper;

    /**
     * Constructor
     *
     * @param Context  $context
     * @param PageFactory $resultPageFactory
     * @param Http $request
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Http $request,
        Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function getQueryChar()
    {
        $this->request->getParams();
        return $this->request->getParam('query');
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $pageTitle = ("Promotions");
        $enabledExtension = $this->helper->isEnabled();

        if ($enabledExtension) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set($pageTitle);

            $this->helper->setQueryChar($this->getQueryChar());

            return $resultPage;
        } else {
            $this->_forward('index', 'noroute', 'cms');
        }
    }
}
