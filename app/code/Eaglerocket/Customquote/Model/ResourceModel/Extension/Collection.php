<?php
namespace Eaglerocket\Customquote\Model\ResourceModel\Extension;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'post_get_id';
	protected $_eventPrefix = 'eaglerocket_customquote_extension_collection';
	protected $_eventObject = 'extension_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Eaglerocket\Customquote\Model\Extension', 'Eaglerocket\Customquote\Model\ResourceModel\Extension');
	}

}
