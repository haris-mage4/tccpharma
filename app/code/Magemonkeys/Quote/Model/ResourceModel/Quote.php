<?php
namespace Magemonkeys\Quote\Model\ResourceModel;


class Quote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('mage_monkeys_quote_status_history', 'entity_id');
	}
	
}
