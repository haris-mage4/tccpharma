<?php
namespace Eaglerocket\Customquote\Model\ResourceModel;


class Extension extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('eaglerocket_customquote_getquote_post', 'post_get_id');
	}
	
}
