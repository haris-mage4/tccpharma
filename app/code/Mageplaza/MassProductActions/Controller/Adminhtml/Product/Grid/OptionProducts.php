<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_MassProductActions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\MassProductActions\Controller\Adminhtml\Product\Grid;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\MassProductActions\Block\Adminhtml\Option\Edit\Renderer\Products as RendererProducts;

/**
 * Class OptionProducts
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product\Grid
 */
class OptionProducts extends Action
{
    /**
     * JS helper
     *
     * @var Js
     */
    protected $_jsonHelper;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * Products constructor.
     *
     * @param Context $context
     * @param JsonHelper $jsonHelper
     * @param PageFactory $pageFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        PageFactory $pageFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->_jsonHelper    = $jsonHelper;
        $this->_pageFactory   = $pageFactory;
        $this->_layoutFactory = $layoutFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $page = $this->_pageFactory->create();
        $html = $page->getLayout()
            ->createBlock(RendererProducts::class)->toHtml();

        if ($this->getRequest()->getParam('loadGrid')) {
            return $this->_layoutFactory->create();
        }

        return $this->getResponse()->representJson($html);
    }
}
