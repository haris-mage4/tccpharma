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

class GetByIdCache implements GetByIdInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GetById
     */
    private $getById;

    public function __construct(Registry $registry, GetById $getById)
    {
        $this->registry = $registry;
        $this->getById = $getById;
    }

    public function execute(int $id): QuoteEntityInterface
    {
        $key = $this->registry->generateKey(QuoteEntityInterface::ENTITY_ID, (string) $id);
        if (!$this->registry->has($key)) {
            $quoteEntity = $this->getById->execute($id);
            $this->registry->save($quoteEntity);
        }

        return $this->registry->get($key);
    }
}
