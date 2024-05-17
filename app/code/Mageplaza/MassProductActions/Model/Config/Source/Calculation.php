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
 * Class Calculation
 * @package Mageplaza\MassProductActions\Model\Config\Source
 */
class Calculation implements ArrayInterface
{
    const PLUS             = 'plus';
    const PLUS_BY_PERCENT  = 'plus-percent';
    const FIXED_VALUE      = 'fixed';
    const MINUS            = 'minus';
    const MINUS_BY_PERCENT = 'minus-percent';

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
            0                      => __('-- Please select --'),
            self::PLUS             => __('Plus'),
            self::PLUS_BY_PERCENT  => __('Plus by Percentage'),
            self::FIXED_VALUE      => __('Fixed value'),
            self::MINUS            => __('Minus'),
            self::MINUS_BY_PERCENT => __('Minus by Percentage')
        ];
    }
}
