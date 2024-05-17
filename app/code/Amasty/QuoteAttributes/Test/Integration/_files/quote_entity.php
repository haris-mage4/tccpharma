<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var QuoteInterface $quote */
$quote = Bootstrap::getObjectManager()->create(QuoteInterface::class);
$quote->setStoreId(1);
/** @var QuoteEntityInterface $quoteEntity */
$quoteEntity = Bootstrap::getObjectManager()->create(QuoteEntityInterface::class);
$quoteEntity->setId(1);
$quote->getExtensionAttributes()->setQuoteEntity($quoteEntity);
$quote->save();
