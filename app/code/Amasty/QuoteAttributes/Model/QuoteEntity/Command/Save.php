<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity\Command;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity as QuoteEntityResource;
use Amasty\QuoteAttributes\Model\QuoteEntity;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
{
    /**
     * @var QuoteEntityResource
     */
    private $quoteEntityResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        QuoteEntityResource $quoteEntityResource,
        LoggerInterface $logger
    ) {
        $this->quoteEntityResource = $quoteEntityResource;
        $this->logger = $logger;
    }

    /**
     * @param QuoteEntityInterface|QuoteEntity $quoteEntity
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(QuoteEntityInterface $quoteEntity): void
    {
        try {
            $this->quoteEntityResource->save($quoteEntity);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Quote Entity'), $e);
        }
    }
}
