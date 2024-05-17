<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Answer;

use Amasty\Customform\Api\Answer\GetAttachedFileUrlInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetAttachedFileUrl implements GetAttachedFileUrlInterface
{
    const AMASTY_CUSTOMFORM_MEDIA_PATH = 'amasty/amcustomform';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function execute(string $fileName, ?int $storeId = null): string
    {
        $store = $this->storeManager->getStore($storeId);
        $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return sprintf('%s%s/%s', $baseUrl, self::AMASTY_CUSTOMFORM_MEDIA_PATH, $fileName);
    }
}
