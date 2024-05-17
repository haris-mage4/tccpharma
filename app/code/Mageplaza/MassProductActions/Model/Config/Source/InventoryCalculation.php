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
 * Class InventoryCalculation
 * @package Mageplaza\MassProductActions\Model\Config\Source
 */
class InventoryCalculation implements ArrayInterface
{
    const PLUS        = 'plus';
    const FIXED_VALUE = 'fixed';
    const MINUS       = 'minus';

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
            self::FIXED_VALUE => __('Fixed value'),
            self::PLUS        => __('Plus'),
            self::MINUS       => __('Minus'),
        ];
    }
}
