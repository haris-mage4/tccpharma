<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Ui\Component\Listing\Column;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * Fix magento bug with function empty
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $item[$this->storeKey] = explode(',', $item[$this->storeKey]);

        return parent::prepareItem($item);
    }
}
