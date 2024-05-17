<?php
namespace Stathmos\QuoteCommunication\Block\Adminhtml\RemarkComment\View\Tab;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Quote\Backend\Session;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Stathmos\QuoteCommunication\Model\RemarkCommentFactory;

class Info extends Template implements TabInterface
{
    protected $_template = 'tab/view/remarkcomment.phtml';
    private Session $quoteSession;
    private RemarkCommentFactory $remarkCommentFactory;

    public function __construct(
        Context $context,
        Session $quoteSession,
        RemarkCommentFactory $RemarkCommentFactory,
        array $data = []
    ) {
        $this->quoteSession = $quoteSession;
        $this->remarkCommentFactory = $RemarkCommentFactory;
        parent::__construct($context, $data);
    }

    public function getRemarkCommentCollection()
    {
        $quoteId = $this->getQuote()->getId();
        if($quoteId){
            $remarkComment = $this->remarkCommentFactory->create();
            return $remarkComment->getCollection()->addFieldToFilter('quote_id', ['eq' => $quoteId]);
        }
    }

    public function getFormAction(): string
    {
        return $this->getUrl('remarkcomment/index/add', ['_secure' => true]);
    }

    public function getSession(): Session
    {
        return $this->quoteSession;
    }

    public function getQuote(): QuoteInterface
    {
        return $this->getSession()->getQuote();
    }

    public function getTabLabel()
    {
        return __('Remark Comment History');
    }

    public function getTabTitle()
    {
        return __('Remark Comment History');
    }

    public function canShowTab(): bool
    {
        return true;
    }

    public function isHidden(): bool
    {
        return false;
    }
}
