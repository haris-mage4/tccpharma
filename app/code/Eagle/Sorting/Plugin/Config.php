<?php

namespace Eagle\Sorting\Plugin;

use Magento\Catalog\Model\Config as catalogConfig;

class Config
{
    /**
     * @param catalogConfig $catalogConfig
     * @param $options
     * @return array
     */
    public function afterGetAttributeUsedForSortByArray(catalogConfig $catalogConfig, $options): array
    {
        $newOptions = ['stock' => __('Availability')];
        unset($options['position']);
        return array_merge($options, $newOptions);
    }
}
