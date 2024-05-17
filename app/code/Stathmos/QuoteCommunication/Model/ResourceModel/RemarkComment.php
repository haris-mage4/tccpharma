<?php
namespace Stathmos\QuoteCommunication\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class RemarkComment extends AbstractDb
{

	public function __construct(
		Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('quote_remark_communication', 'id');
	}

}
