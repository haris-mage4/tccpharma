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

namespace Mageplaza\MassProductActions\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Store\Switcher;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as ActionAttribute;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\MassProductActions\Block\Adminhtml\Attribute\Edit;
use Mageplaza\MassProductActions\Model\Config\Source\System\Actions;

/**
 * Class Load
 * @package Mageplaza\MassProductActions\Controller\Adminhtml\Product\Attribute
 */
class Load extends Attribute
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Json
     */
    protected $_resultJson;

    /**
     * @var Layout
     */
    protected $_layout;

    /**
     * Load constructor.
     *
     * @param Context $context
     * @param ActionAttribute $attributeHelper
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Json $resultJson
     * @param Layout $layout
     */
    public function __construct(
        Context $context,
        ActionAttribute $attributeHelper,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Json $resultJson,
        Layout $layout
    ) {
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_resultJson        = $resultJson;
        $this->_layout            = $layout;

        parent::__construct($context, $attributeHelper);
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('filters')) {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $this->attributeHelper->setProductIds($collection->getAllIds());
        }
        if (!$this->_validateProducts()) {
            return $this->_resultJson->setData([
                'status'       => false,
                'redirect_url' => $this->getUrl('catalog/product/', ['_current' => true])
            ]);
        }
        $attrEditBlock = $this->_layout
            ->createBlock(Edit::class);
        /** @var Switcher $storeSwitcherBlock */
        $storeSwitcherBlock = $this->_layout->createBlock(Switcher::class)
            ->setUseConfirm(1)
            ->setPopupType(Actions::UPDATE_ATTRIBUTES)
            ->setTemplate('Mageplaza_MassProductActions::store/switcher.phtml');

        return $this->_resultJson->setData([
            'status'      => true,
            'store_html'  => $storeSwitcherBlock->toHtml(),
            'result_html' => $attrEditBlock->toHtml()
        ]);
    }
}
