<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Query;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity;
use Magento\Eav\Api\AttributeRepositoryInterface;

class GetByCode implements GetByCodeInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $eavAttributeRepository;

    public function __construct(AttributeRepositoryInterface $eavAttributeRepository)
    {
        $this->eavAttributeRepository = $eavAttributeRepository;
    }

    public function execute(string $attributeCode): AttributeInterface
    {
        return $this->eavAttributeRepository->get(QuoteEntity::TYPE_CODE, $attributeCode);
    }
}
