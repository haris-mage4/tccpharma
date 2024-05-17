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
 * @category  Mageplaza
 * @package   Mageplaza_MassProductActions
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\MassProductActions\Test\Unit\Model\Config\Source\System;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mageplaza\MassProductActions\Model\Config\Source\System\Actions;
use PHPUnit\Framework\TestCase;

/**
 * Class ActionsTest
 * @package Mageplaza\MassProductActions\Test\Unit\Model\Config\Source\System
 */
class ActionsTest extends TestCase
{
    /**
     * @var Actions
     */
    protected $model;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->model = $helper->getObject(Actions::class);
    }

    /**
     * Test to actions option array
     */
    public function testToOptionArray()
    {
        $expectResult = [
            [
                'value' => 1,
                'label' => __('Change Attribute Set'),
                'type'  => 'mp_change_attribute_set'
            ],
            [
                'value' => 2,
                'label' => __('Quick Attributes Update'),
                'type'  => 'mp_update_attributes'
            ],
            [
                'value' => 3,
                'label' => __('Update Category'),
                'type'  => 'mp_update_category'
            ],
            [
                'value' => 4,
                'label' => __('Update Cross-sell Products'),
                'type'  => 'mp_update_cross_sell_product'
            ],
            [
                'value' => 5,
                'label' => __('Copy Custom Options'),
                'type'  => 'mp_update_custom_options'
            ],
            [
                'value' => 6,
                'label' => __('Add Custom Options'),
                'type'  => 'mp_add_custom_options'
            ],
            [
                'value' => 7,
                'label' => __('Remove Custom Options'),
                'type'  => 'mp_remove_custom_options'
            ],
            [
                'value' => 8,
                'label' => __('Update Images'),
                'type'  => 'mp_update_images'
            ],
            [
                'value' => 9,
                'label' => __('Update Inventory'),
                'type'  => 'mp_update_inventory'
            ],
            [
                'value' => 10,
                'label' => __('Update Price'),
                'type'  => 'mp_update_price'
            ],
            [
                'value' => 11,
                'label' => __('Update Related Products'),
                'type'  => 'mp_update_related_product'
            ],
            [
                'value' => 12,
                'label' => __('Update Up-sell Products'),
                'type'  => 'mp_update_up_sell_product'
            ],
            [
                'value' => 13,
                'label' => __('Update Website'),
                'type'  => 'mp_update_website'
            ],
        ];
        $actualResult = $this->model->toOptionArray();
        $this->assertEquals($expectResult, $actualResult);
    }
}
