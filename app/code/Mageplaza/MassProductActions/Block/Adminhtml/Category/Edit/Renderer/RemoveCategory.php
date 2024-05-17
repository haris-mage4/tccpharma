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

namespace Mageplaza\MassProductActions\Block\Adminhtml\Category\Edit\Renderer;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\MassProductActions\Helper\Data as HelperData;

/**
 * Class Category
 * @package Mageplaza\MassProductActions\Block\Adminhtml\Category\Edit\Renderer
 */
class RemoveCategory extends Multiselect
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Category constructor.
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
        array $data = []
    ) {
        $this->_helperData = $helperData;

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
        $html .= '<div id="mp-product-remove-category-select" class="admin__field" 
data-bind="scope:\'productRemoveCategory\'" data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input name="product[remove_category_ids]" data-bind="value: value" style="display: none"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div></div>';
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
                            "productRemoveCategory": {
                                "component": "uiComponent",
                                "children": {
                                    "product_select_remove_category": {
                                        "component": "Magento_Catalog/js/components/new-category",
                                        "config": {
                                            "filterOptions": true,
                                            "disableLabel": true,
                                            "chipsEnabled": true,
                                            "levelsVisibility": "1",
                                            "elementTmpl": "ui/grid/filters/elements/ui-select",
                                            "options": ' . $this->_helperData->getCategoriesTree() . ',
                                            "listens": {
                                                "index=create_category:responseData": "setParsed"
                                            },
                                            "config": {
                                                "dataScope": "product_select_remove_category",
                                                "sortOrder": 10
                                            }
                                        }
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
}
