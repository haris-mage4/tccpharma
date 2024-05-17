<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Zip;

use Magento\Framework\Stdlib\DateTime\Timezone;

class ZipFileNameGenerator
{
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(
        Timezone $timezone
    ) {
        $this->timezone = $timezone;
    }

    public function generate(string $prefix): string
    {
        $date = $this->timezone->date();
        $namePostfix = $date->format('d-m-Y');

        return sprintf('%s_%s.zip', $prefix, $namePostfix);
    }
}
