<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Email;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\ViewModel\QuoteLoader;
use Magento\Sales\Block\Items\AbstractItems;

class Items extends AbstractItems
{

    public function getQuote(): ?QuoteInterface
    {
        $quoteId = $this->getData('quote')->getId();

        /** @var QuoteLoader $quoteLoader */
       $quoteLoader = $this->getData('quote_loader');

        if ($quoteId && $quoteLoader) {
            return $quoteLoader->load((int) $quoteId);
        }

        return $quoteId;
    }

    public function getId(){
        return $quoteId = $this->getData('quote')->getId();
    } 

    public function getRemarkCommentCollection()
	{
		$quoteId = $this->getData('quote')->getId();
	
		if($quoteId){
            $remarkCommentFactory = \Magento\Framework\App\ObjectManager::getInstance()->create('Stathmos\QuoteCommunication\Model\RemarkCommentFactory');
            $remarkComment = $remarkCommentFactory->create();
			$quoteRemarkcollection = $remarkComment->getCollection()->addFieldToFilter('quote_id', ['eq' => $quoteId]);
			return $quoteRemarkcollection;
		}
	}
}
