<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Quote;

use Amasty\QuoteAttributes\Api\QuoteEntityRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use Magento\Quote\Model\Quote;

class QuoteEntityRelation implements RelationInterface
{
    /**
     * @var QuoteEntityRepositoryInterface
     */
    private $quoteEntityRepository;

    public function __construct(QuoteEntityRepositoryInterface $quoteEntityRepository)
    {
        $this->quoteEntityRepository = $quoteEntityRepository;
    }

    /**
     * @param AbstractModel|Quote $object
     * @return void
     * @throws CouldNotSaveException
     */
    public function processRelation(AbstractModel $object): void
    {
        if ($quoteEntity = $object->getExtensionAttributes()->getQuoteEntity()) {
            if ($quoteEntity->getQuoteId() === null) {
                $quoteEntity->setQuoteId((int) $object->getEntityId());
            }
            $this->quoteEntityRepository->save($quoteEntity);
        }
    }
}
