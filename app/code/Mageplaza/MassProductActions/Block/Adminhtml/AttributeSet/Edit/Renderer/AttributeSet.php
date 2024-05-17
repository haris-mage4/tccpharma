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

namespace Mageplaza\MassProductActions\Block\Adminhtml\AttributeSet\Edit\Renderer;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttrSetCol;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttrSetColFact;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Form\Field;

/**
 * Class AttributeSet
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Post\Edit\Renderer
 */
class AttributeSet extends Select
{
    /**
     * @var AttrSetColFact
     */
    protected $_attrSetColFact;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * AttributeSet constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param AttrSetColFact $attrSetColFact
     * @param Config $eavConfig
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        AttrSetColFact $attrSetColFact,
        Config $eavConfig,
        array $data = []
    ) {
        $this->_attrSetColFact = $attrSetColFact;
        $this->_eavConfig      = $eavConfig;

        parent::__construct(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $data
        );
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getElementHtml()
    {
        $html = '<div class="admin__field-control admin__control-grouped">';
        $html .= '<div id="mp-attribute-set-select" class="admin__field" data-bind="scope:\'attributeSet\'" 
data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input name="product[attribute_set_id]" data-bind="value: value" style="display: none"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div>';
        $html .= '</div>';

        $html .= $this->getAfterElementHtml();

        return $html;
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     */
    public function getAfterElementHtml()
    {
        $html = '<script type="text/x-magento-init">
            {
                "*": {
                    "Magento_Ui/js/core/app": {
                        "components": {
                            "attributeSet": {
                                "component": "uiComponent",
                                "children": {
                                    "attribute_set_id": {
                                        "component": "Mageplaza_MassProductActions/js/components/attribute-set-select",
                                        "filterOptions": true,
                                        "disableLabel": true,
                                        "elementTmpl": "ui/grid/filters/elements/ui-select",
                                        "formElement" : "select",
                                        "componentType" : "' . Field::NAME . '",
                                        "multiple" : false,
                                        "options": ' . json_encode($this->getOptions()) . '
                                    }
                                }
                            }
                        }
                    }
                }
            }
        </script>';

        return $html;
    }

    /**
     * Return options for select
     *
     * @return array
     * @throws LocalizedException
     */
    public function getOptions()
    {
        $entityTypeId = $this->_eavConfig->getEntityType(Product::ENTITY)->getEntityTypeId();
        /** @var AttrSetCol $collection */
        $collection = $this->_attrSetColFact->create();
        $collection->setEntityTypeFilter($entityTypeId)
            ->addFieldToSelect('attribute_set_id', 'value')
            ->addFieldToSelect('attribute_set_name', 'label')
            ->setOrder(
                'attribute_set_name',
                AttrSetCol::SORT_ORDER_ASC
            );

        return $collection->getData();
    }
}
