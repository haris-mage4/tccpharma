<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Repository;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;
use Amasty\QuoteAttributes\Model\QuoteEntity\Command\Save as SaveCommand;
use Amasty\QuoteAttributes\Model\QuoteEntity\Query\GetByIdInterface;
use Amasty\QuoteAttributes\Model\QuoteEntity\Query\GetByQuoteIdInterface;
use Amasty\QuoteAttributes\Model\QuoteEntity\Query\GetNewInterface;

class QuoteEntityRepository implements QuoteEntityRepositoryInterface
{
    /**
     * @var GetByIdInterface
     */
    private $getById;

    /**
     * @var GetByQuoteIdInterface
     */
    private $getByQuoteId;

    /**
     * @var SaveCommand
     */
    private $saveCommand;

    /**
     * @var GetNewInterface
     */
    private $getNew;

    public function __construct(
        GetByIdInterface $getById,
        GetByQuoteIdInterface $getByQuoteId,
        GetNewInterface $getNew,
        SaveCommand $saveCommand
    ) {
        $this->getById = $getById;
        $this->getByQuoteId = $getByQuoteId;
        $this->saveCommand = $saveCommand;
        $this->getNew = $getNew;
    }

    public function get(int $id): QuoteEntityInterface
    {
        return $this->getById->execute($id);
    }

    public function getByQuoteId(int $quoteId): QuoteEntityInterface
    {
        return $this->getByQuoteId->execute($quoteId);
    }

    public function getNew(): QuoteEntityInterface
    {
        return $this->getNew->execute();
    }

    public function save(QuoteEntityInterface $quoteEntity): void
    {
        $this->saveCommand->execute($quoteEntity);
    }
}
