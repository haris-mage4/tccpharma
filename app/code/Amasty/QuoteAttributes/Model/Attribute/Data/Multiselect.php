<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Attribute\Data;

use Magento\Eav\Model\Attribute\Data\Multiselect as EavMultiselect;
use Magento\Framework\App\RequestInterface;

class Multiselect extends EavMultiselect
{
    /**
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function extractValue(RequestInterface $request)
    {
        return $this->_getRequestValue($request);
    }
}
