<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Answer\GoogleMap\Api;

use GuzzleHttp\Promise\PromiseInterface;

interface RequestByCoordinatesInterface
{
    /**
     * @param float $longitude
     * @param float $latitude
     * @return PromiseInterface
     */
    public function requestByCoordinates(float $longitude, float $latitude): PromiseInterface;
}
