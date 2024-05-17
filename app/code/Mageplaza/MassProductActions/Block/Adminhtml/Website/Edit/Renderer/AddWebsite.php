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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Website\Edit\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Mageplaza\MassProductActions\Helper\Data as HelperData;

/**
 * Class AddWebsite
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Website\Edit\Renderer
 */
class AddWebsite extends AbstractElement
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * ShippingMethod constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        HelperData $helperData,
        $data = []
    ) {
        $this->_helperData = $helperData;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('note');
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<div class="store-scope"><div class="store-tree" id="add-products-to-website-content">';
        foreach ($this->_helperData->getWebsiteCollection() as $_website) {
            /** @var Website $_website */
            $html .= '<div class="website-name">';
            $html .= '<input name="product[add_website_ids][]" value="' . $_website->getId() . '" 
            class="checkbox website-checkbox" id="add_product_website_' . $_website->getId() . '" type="checkbox" />';
            $html .= '<label for="add_product_website_' . $_website->getId() . '">' . $_website->getName() . '</label>';
            $html .= '</div>';
            $html .= '<dl class="website-groups" id="add_product_website_' . $_website->getId() . '_data">';
            foreach ($this->_helperData->getGroupCollection($_website) as $_group) {
                /** @var Group $_group */
                $html .= '<dt>' . $_group->getName() . '</dt>';
                $html .= '<dd class="group-stores"><ul>';
                foreach ($this->_helperData->getStoreCollection($_group) as $_store) {
                    /** @var Store $_store */
                    $html .= '<li>' . $_store->getName() . '</li>';
                }
                $html .= '</ul></dd>';
            }
            $html .= '</dl>';
        }
        $html .= '</div></div>';

        return $html;
    }
}
