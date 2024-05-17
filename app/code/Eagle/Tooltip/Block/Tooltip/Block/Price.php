<?php

/**
 * Copyright © Eagle All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Eagle\Tooltip\Block\Tooltip\Block;

use Eagle\Tooltip\Helper\Config\TooltipConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Price extends Template
{
    /**
     * @var TooltipConfig
     */
    protected $tooltipConfig;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param Context $context
     * @param TooltipConfig $tooltipConfig
     * @param Session $customerSession
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        TooltipConfig $tooltipConfig,
        Session $customerSession,
        Registry $registry,
        array $data = []
    ) {
        $this->tooltipConfig = $tooltipConfig;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string|null
     */
    public function getRegularTooltipText(): ?string
    {
        return $this->tooltipConfig->getRegularPriceText();
    }

    /**
     * @return string|null
     */
    public function getSpecialPriceText(): ?string
    {
        return $this->tooltipConfig->getSpecialPriceText();
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get the current product
     *
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Check if the product has advanced price
     *
     * @param $product
     * @return bool
     */
    public function hasAdvancedPrice($product): bool
    {
        $tierPrices = $product->getTierPrices();

        foreach ($tierPrices as $tierPrice) {
            if ($tierPrice->getCustomerGroupId() != 0) {
                return true;
            }
        }

        return false;
    }
}
