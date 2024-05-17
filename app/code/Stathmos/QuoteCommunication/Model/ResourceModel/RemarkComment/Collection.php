<?php
namespace Stathmos\QuoteCommunication\Model\ResourceModel\RemarkComment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Stathmos\QuoteCommunication\Model\RemarkComment', 'Stathmos\QuoteCommunication\Model\ResourceModel\RemarkComment');
	}

}
