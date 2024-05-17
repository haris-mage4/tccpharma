<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Command;

use Amasty\QuoteAttributes\Model\Attribute\Query\GetByIdInterface;

class DeleteById implements DeleteByIdInterface
{
    /**
     * @var GetByIdInterface
     */
    private $getById;

    /**
     * @var DeleteInterface
     */
    private $delete;

    public function __construct(GetByIdInterface $getById, DeleteInterface $delete)
    {
        $this->getById = $getById;
        $this->delete = $delete;
    }

    public function execute(int $id): void
    {
        $attribute = $this->getById->execute($id);
        $this->delete->execute($attribute);
    }
}
