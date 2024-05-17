<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Command;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

interface DeleteByIdInterface
{
    /**
     * @param int $id
     * @return void
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function execute(int $id): void;
}
