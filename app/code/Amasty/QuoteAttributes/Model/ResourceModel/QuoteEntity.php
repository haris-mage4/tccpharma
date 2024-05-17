<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\ResourceModel;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;

class QuoteEntity extends \Magento\Eav\Model\Entity\AbstractEntity
{
    public const TABLE_NAME = 'amasty_quote_attribute_entity';
    public const TYPE_CODE = 'amasty_quote';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setType(self::TYPE_CODE);
    }

    /**
     * @return string[]
     */
    protected function _getDefaultAttributes()
    {
        return [QuoteEntityInterface::QUOTE_ID];
    }
}
