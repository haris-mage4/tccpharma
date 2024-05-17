<?php

namespace Magemonkeys\Customerinfo\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Customerinfo  extends Template
{
    /**
     * @var Session
     */
    protected $_session;

    public function __construct(
        Context $context,
        Session $session,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_session = $session;
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        if ($this->_session->isLoggedIn()) {
            return $this->_session->getCustomer()->getGroupId();
        } else {
            return 0;
        }
    }
}
