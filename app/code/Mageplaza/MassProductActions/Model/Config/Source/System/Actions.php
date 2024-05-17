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

namespace Mageplaza\MassProductActions\Model\Config\Source\System;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Actions
 * @package Mageplaza\MassProductActions\Model\Config\Source\System
 */
class Actions implements ArrayInterface
{
    const CHANGE_ATTRIBUTE_SET      = 1;
    const UPDATE_ATTRIBUTES         = 2;
    const UPDATE_CATEGORY           = 3;
    const UPDATE_CROSS_SELL_PRODUCT = 4;
    const COPY_CUSTOM_OPTIONS       = 5;
    const ADD_CUSTOM_OPTIONS        = 6;
    const REMOVE_CUSTOM_OPTIONS     = 7;
    const UPDATE_IMAGES             = 8;
    const UPDATE_INVENTORY          = 9;
    const UPDATE_PRICE              = 10;
    const UPDATE_RELATED_PRODUCT    = 11;
    const UPDATE_UP_SELL_PRODUCT    = 12;
    const UPDATE_WEBSITE            = 13;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label['label'],
                'type'  => $label['type']
            ];
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
            self::CHANGE_ATTRIBUTE_SET      => [
                'label' => __('Change Attribute Set'),
                'type'  => 'mp_change_attribute_set'
            ],
            self::UPDATE_ATTRIBUTES         => [
                'label' => __('Quick Attributes Update'),
                'type'  => 'mp_update_attributes'
            ],
            self::UPDATE_CATEGORY           => [
                'label' => __('Update Category'),
                'type'  => 'mp_update_category'
            ],
            self::UPDATE_CROSS_SELL_PRODUCT => [
                'label' => __('Update Cross-sell Products'),
                'type'  => 'mp_update_cross_sell_product'
            ],
            self::COPY_CUSTOM_OPTIONS       => [
                'label' => __('Copy Custom Options'),
                'type'  => 'mp_update_custom_options'
            ],
            self::ADD_CUSTOM_OPTIONS        => [
                'label' => __('Add Custom Options'),
                'type'  => 'mp_add_custom_options'
            ],
            self::REMOVE_CUSTOM_OPTIONS     => [
                'label' => __('Remove Custom Options'),
                'type'  => 'mp_remove_custom_options'
            ],
            self::UPDATE_IMAGES             => [
                'label' => __('Update Images'),
                'type'  => 'mp_update_images'
            ],
            self::UPDATE_INVENTORY          => [
                'label' => __('Update Inventory'),
                'type'  => 'mp_update_inventory'
            ],
            self::UPDATE_PRICE              => [
                'label' => __('Update Price'),
                'type'  => 'mp_update_price'
            ],
            self::UPDATE_RELATED_PRODUCT    => [
                'label' => __('Update Related Products'),
                'type'  => 'mp_update_related_product'
            ],
            self::UPDATE_UP_SELL_PRODUCT    => [
                'label' => __('Update Up-sell Products'),
                'type'  => 'mp_update_up_sell_product'
            ],
            self::UPDATE_WEBSITE            => [
                'label' => __('Update Website'),
                'type'  => 'mp_update_website'
            ],
        ];
    }
}
