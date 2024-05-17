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
use Magento\Framework\Exception\NoSuchEntityException;

class GetById implements GetByIdInterface
{
    /**
     * @var QuoteEntityInterfaceFactory
     */
    private $quoteEntityFactory;

    /**
     * @var QuoteEntityResource
     */
    private $quoteEntityResource;

    public function __construct(
        QuoteEntityInterfaceFactory $quoteEntityFactory,
        QuoteEntityResource $quoteEntityResource
    ) {
        $this->quoteEntityFactory = $quoteEntityFactory;
        $this->quoteEntityResource = $quoteEntityResource;
    }

    public function execute(int $id): QuoteEntityInterface
    {
        /** @var QuoteEntityInterface|QuoteEntity $quoteEntity */
        $quoteEntity = $this->quoteEntityFactory->create();
        $this->quoteEntityResource->load($quoteEntity, $id);

        if ($quoteEntity->getId() === null) {
            throw new NoSuchEntityException(
                __('Quote Entity with id "%value" does not exist.', ['value' => $id])
            );
        }

        return $quoteEntity;
    }
}
