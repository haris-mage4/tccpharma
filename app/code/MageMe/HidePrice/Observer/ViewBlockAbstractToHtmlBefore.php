<?php
namespace MageMe\HidePrice\Observer;

use MageMe\HidePrice\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Template;
use function var_dump;

class ViewBlockAbstractToHtmlBefore implements ObserverInterface
{
    /** @var Data */
    private $helper;

    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        /** @var Template $block */
        $block = $observer->getData('block');
        if($this->helper->hidePrice()) {
            if (strstr($block->getTemplate(), 'addtocart')) {
                //$block->setTemplate('');
            }
        }
    }
}
