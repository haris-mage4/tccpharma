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

use Magento\Ui\Component\MassAction as ComponentMassAction;

/**
 * Class OldMassAction
 * @package Mageplaza\MassProductActions\Plugin\Component
 */
class OldMassAction extends AbstractMassAction
{
    /**
     * @param ComponentMassAction $massAction
     */
    public function afterPrepare(ComponentMassAction $massAction)
    {
        if ($this->_helperData->isEnabled() && $this->_request->getFullActionName() === 'catalog_product_index') {
            $this->addMassActions($massAction);
        }
    }
}
