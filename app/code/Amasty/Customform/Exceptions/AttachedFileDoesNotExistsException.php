<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Exceptions;

use Magento\Framework\Exception\LocalizedException;

class AttachedFileDoesNotExistsException extends LocalizedException
{
    //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function forFile(string $fileName): AttachedFileDoesNotExistsException
    {
        return new AttachedFileDoesNotExistsException(__('Attached file %1 does not exists.', $fileName));
    }
}
