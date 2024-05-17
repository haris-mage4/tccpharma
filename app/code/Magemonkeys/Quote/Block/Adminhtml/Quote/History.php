<?php

namespace Magemonkeys\Quote\Block\Adminhtml\Quote;
use Magemonkeys\Quote\Model\ResourceModel\Quote\CollectionFactory;

class History extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CollectionFactory $_collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $_collectionFactory;
        parent::__construct($context,$data);
    }

    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('order_history_block').parentNode, '" . $this->getSubmitUrl() . "')";
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            ['label' => __('Submit Comment'), 'class' => 'action-save action-secondary', 'onclick' => $onclick]
        );
        $this->setChild('submit_button', $button);
        return parent::_prepareLayout();
    }
    public function getSubmitUrl()
    {
        return $this->getUrl('magemonkey_quote/*/edit', ['quote_id' =>  $this->getRequest()->getParam('quote_id')]);
    }
   
    public function getAdminRemarkHistory(){
    
        return $this->_collectionFactory->create()->addFieldToFilter('quote_id',$this->getRequest()->getParam('quote_id'));

    }

    // public function getTemplate()
    // {
    //     return 'Magemonkeys_Quote::quote/edit/history.phtml';
    // }
}
