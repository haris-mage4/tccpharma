<?php
namespace  Magemonkeys\Quote\Model\ResourceModel\Quote;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'entity_id';
	protected $_eventPrefix = 'mage_monkeys_quote_status_history';
	protected $_eventObject = 'post_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Magemonkeys\Quote\Model\Quote', 'Magemonkeys\Quote\Model\ResourceModel\Quote');
	}

}

