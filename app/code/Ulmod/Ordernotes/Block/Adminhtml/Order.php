<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin as AdminHelper;
use Ulmod\Ordernotes\Model\NotesFactory;
    
/**
 * Order block
 */
class Order extends Template implements TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'tab/notes.phtml';

    /**
     * @var AdminHelper
     */
    private $adminHelper;

    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var NotesFactory
     */
    protected $notesFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param AdminHelper $adminHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AdminHelper $adminHelper,
        NotesFactory $notesFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->notesFactory = $notesFactory;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Notes');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        $orderId =  $this->getOrder()->getId();
        $notesCount = $this->notesFactory->create()
            ->getNotesCount($orderId);
            
        return __('Order Notes (%1)', $notesCount);
    }
    
    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    
    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        $orderId = $this->getOrder()->getId();
        return $this->getUrl(
            'ulmod_ordernotes/notes/view',
            ['_current' => true, 'id' => $orderId]
        );
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }
    
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }
}
