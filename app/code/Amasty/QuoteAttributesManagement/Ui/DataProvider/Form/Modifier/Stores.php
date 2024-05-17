<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attribute Management for Magento 2 (System)
 */

namespace Amasty\QuoteAttributesManagement\Ui\DataProvider\Form\Modifier;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute\Store\GetByAttributeId as GetStoresByAttributeId;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Stores implements ModifierInterface
{
    public const FIELD_DATA_SCOPE = 'stores';

    /**
     * @var GetStoresByAttributeId
     */
    private $getStoresByAttributeId;

    public function __construct(GetStoresByAttributeId $getStoresByAttributeId)
    {
        $this->getStoresByAttributeId = $getStoresByAttributeId;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        $attributeId = (int) $data[AttributeInterface::ATTRIBUTE_ID];
        $stores = $this->getStoresByAttributeId->execute($attributeId);
        if ($stores) {
            $data[self::FIELD_DATA_SCOPE] = implode(',', $stores);
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
