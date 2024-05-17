<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Answer\GoogleMap\Api;

interface ResponseProcessorInterface
{
    /**
     * @param string $response
     * @return string
     */
    public function processResponse(string $response): string;
}
