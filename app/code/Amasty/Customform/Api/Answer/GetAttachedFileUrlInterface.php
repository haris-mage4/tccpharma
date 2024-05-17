<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Api\Answer;

/**
 * @api
 */
interface GetAttachedFileUrlInterface
{
    /**
     * If store id is not passed, current store will be used
     *
     * @param string $fileName
     * @param int|null $storeId
     * @return string
     */
    public function execute(string $fileName, ?int $storeId = null): string;
}
