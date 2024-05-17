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

namespace Mageplaza\MassProductActions\Test\Unit\Helper;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryColFact;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mageplaza\MassProductActions\Helper\Data as HelperData;
use Mageplaza\MassProductActions\Model\Config\Source\System\Actions;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class DataTest
 * @package Mageplaza\MassProductActions\Test\Unit\Helper
 */
class DataTest extends TestCase
{
    /**
     * @var CategoryColFact|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_categoryColFactMock;

    /**
     * @var Actions|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_massActionsMock;

    /**
     * @var HelperData
     */
    protected $model;

    protected function setUp()
    {
        $this->_categoryColFactMock = $this->getMockBuilder(CategoryColFact::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->_massActionsMock = $this->getMockBuilder(Actions::class)
            ->disableOriginalConstructor()
            ->setMethods(['toOptionArray'])
            ->getMock();

        $helper = new ObjectManager($this);

        $this->model = $helper->getObject(
            HelperData::class,
            [
                '_categoryColFact' => $this->_categoryColFactMock,
                '_massActions'     => $this->_massActionsMock
            ]
        );
    }

    /**
     * Test get submit product html
     */
    public function testGetSubmitProductHtml()
    {
        $url          = 'sample_url';
        $expectResult = '<button type="button" class="mp_submit_products_grid" style="display: none;" onclick="mpMassProductAction.submitProductsGrid(event);this.hide();">
                        <span>' . __('Submit') . '</span>
              </button>
              <div class="mpmassproductactions_image_loader">
                    <div class="loader">
                            <img src="sample_url"
                                 alt="' . __('Loading') . '">
                    </div>
              </div>';
        $actualResult = $this->model->getSubmitProductHtml($url);

        $this->assertEquals($expectResult, $actualResult);
    }

    /**
     * Test get select product html
     */
    public function testGetSelectProductHtml()
    {
        $onclickText  = 'sample_onclick';
        $expectResult = '<button type="button" class="mp_load_products_grid" 
                        onclick="sample_onclick">
                    <span>' . __('Select') . '</span>
                </button>';
        $actualResult = $this->model->getSelectProductHtml($onclickText);

        $this->assertEquals($expectResult, $actualResult);
    }
}
