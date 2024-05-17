<?php
namespace Eagle\CustomSearchBlock\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Ranges
 */
class Ranges extends AbstractFieldArray
{

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('search_term', ['label' => __('Search Term'), 'class' => 'required-entry']);
        $this->addColumn('content', ['label' => __('Content'), 'class' => 'required-entry', 'type' => 'textarea']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
