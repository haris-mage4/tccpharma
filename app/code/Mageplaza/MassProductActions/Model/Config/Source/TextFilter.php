<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_MassProductActions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\MassProductActions\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TextFilter
 * @package Mageplaza\MassProductActions\Model\Config\Source
 */
class TextFilter implements ArrayInterface
{
    const REPLACE              = 0;
    const UPPER_CASE           = 1;
    const LOWER_CASE           = 2;
    const CAPITALIZE_EACH_WORD = 3;
    const TOGGLE_CASE          = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = compact('value', 'label');
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::REPLACE              => __('Replace'),
            self::UPPER_CASE           => __('UPPER CASE'),
            self::LOWER_CASE           => __('lower case'),
            self::CAPITALIZE_EACH_WORD => __('Capitalize Each Word'),
            self::TOGGLE_CASE          => __('tOGGLE cASE'),
        ];
    }
}
