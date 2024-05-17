<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Extensions\Stores;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute\Store\SaveMultiple;
use Zend_Db_Exception;

class SaveHandler
{
    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    public function __construct(SaveMultiple $saveMultiple)
    {
        $this->saveMultiple = $saveMultiple;
    }

    /**
     * @param AttributeInterface $attribute
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(AttributeInterface $attribute): void
    {
        $extensionAttributes = $attribute->getExtensionAttributes();
        $stores = $extensionAttributes->getAmastyStores();

        if ($stores !== null) {
            $this->saveMultiple->execute((int) $attribute->getAttributeId(), $stores);
        }
    }
}
