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

namespace Mageplaza\MassProductActions\Plugin\Component;

use Magento\Catalog\Ui\Component\Product\MassAction as ProductMassAction;

/**
 * Class MassAction
 * @package Mageplaza\MassProductActions\Plugin\Component
 */
class MassAction extends AbstractMassAction
{
    /**
     * @param ProductMassAction $massAction
     */
    public function afterPrepare(ProductMassAction $massAction)
    {
        if ($this->_helperData->isEnabled() && $this->_request->getFullActionName() === 'catalog_product_index') {
            $this->addMassActions($massAction);
        }
    }
}
