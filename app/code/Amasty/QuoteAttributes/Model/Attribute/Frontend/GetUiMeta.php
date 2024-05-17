<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Frontend;

class GetUiMeta
{
    /**
     * @var array
     */
    private $uiMeta;

    public function __construct(array $uiMeta)
    {
        $this->uiMeta = $uiMeta;
    }

    /**
     * @param string $frontendType
     * @return array|null
     */
    public function execute(string $frontendType): ?array
    {
        return $this->uiMeta[$frontendType] ?? null;
    }
}
