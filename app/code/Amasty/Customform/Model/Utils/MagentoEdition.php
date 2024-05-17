<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Utils;

use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;

class MagentoEdition
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

    public function isEnterpriseVersion(): bool
    {
        return $this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME;
    }
}
