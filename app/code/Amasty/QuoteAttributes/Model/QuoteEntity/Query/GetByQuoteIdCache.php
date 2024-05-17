<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity\Query;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Model\QuoteEntity\Registry;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByQuoteIdCache implements GetByQuoteIdInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GetByQuoteId
     */
    private $getByQuoteId;

    public function __construct(Registry $registry, GetByQuoteId $getByQuoteId)
    {
        $this->registry = $registry;
        $this->getByQuoteId = $getByQuoteId;
    }

    public function execute(int $quoteId): QuoteEntityInterface
    {
        $key = $this->registry->generateKey(QuoteEntityInterface::QUOTE_ID, (string) $quoteId);
        if (!$this->registry->has($key)) {
            $quoteEntity = $this->getByQuoteId->execute($quoteId);
            $this->registry->save($quoteEntity);
        }

        return $this->registry->get($key);
    }
}
