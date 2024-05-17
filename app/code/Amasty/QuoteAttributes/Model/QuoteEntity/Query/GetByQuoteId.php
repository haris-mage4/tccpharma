<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity\Query;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterfaceFactory;
use Amasty\QuoteAttributes\Model\QuoteEntity;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity as QuoteEntityResource;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity\FindQuoteEntityId;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByQuoteId implements GetByQuoteIdInterface
{
    /**
     * @var QuoteEntityInterfaceFactory
     */
    private $quoteEntityFactory;

    /**
     * @var QuoteEntityResource
     */
    private $quoteEntityResource;

    /**
     * @var FindQuoteEntityId
     */
    private $findQuoteEntityId;

    public function __construct(
        QuoteEntityInterfaceFactory $quoteEntityFactory,
        QuoteEntityResource $quoteEntityResource,
        FindQuoteEntityId $findQuoteEntityId
    ) {
        $this->quoteEntityFactory = $quoteEntityFactory;
        $this->quoteEntityResource = $quoteEntityResource;
        $this->findQuoteEntityId = $findQuoteEntityId;
    }

    public function execute(int $quoteId): QuoteEntityInterface
    {
        $quoteId = $this->findQuoteEntityId->execute(QuoteEntityInterface::QUOTE_ID, (string) $quoteId);
        if (!$quoteId) {
            $this->throwNoSuchException($quoteId);
        }

        /** @var QuoteEntityInterface|QuoteEntity $quoteEntity */
        $quoteEntity = $this->quoteEntityFactory->create();
        $this->quoteEntityResource->load($quoteEntity, $quoteId);
        if ($quoteEntity->getId() === null) {
            $this->throwNoSuchException($quoteId);
        }

        return $quoteEntity;
    }

    /**
     * @param int $quoteId
     * @return void
     * @throws NoSuchEntityException
     */
    private function throwNoSuchException(int $quoteId): void
    {
        throw new NoSuchEntityException(
            __('Quote Entity with quote id "%value" does not exist.', ['value' => $quoteId])
        );
    }
}
