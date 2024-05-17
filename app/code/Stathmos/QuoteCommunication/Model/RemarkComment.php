<?php
namespace Stathmos\QuoteCommunication\Model;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class RemarkComment extends AbstractModel implements IdentityInterface
{
	const CACHE_TAG = 'quote_remark_communication';

	protected function _construct()
	{
		$this->_init('Stathmos\QuoteCommunication\Model\ResourceModel\RemarkComment');
	}

	public function getIdentities(): array
    {
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

}
