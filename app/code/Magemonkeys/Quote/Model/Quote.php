<?php
namespace Magemonkeys\Quote\Model;
class Quote extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'mage_monkeys_quote_status_history';

	protected $_cacheTag = 'mage_monkeys_quote_status_history';

	protected $_eventPrefix = 'mage_monkeys_quote_status_history';

	protected function _construct()
	{
		$this->_init('Magemonkeys\Quote\Model\ResourceModel\Quote');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
