<?php
namespace Magemonkeys\Customerinfo\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Header\Logo;

class Homeinfo  extends Template
{
    /**
     * @var Logo
     */
    protected Logo $logo;

    /**
     * @param Context $context
     * @param Logo $logo
     * @param array $data
     */
    public function __construct(
        Context $context,
        Logo $logo,
        array $data = []
    )
    {
        $this->logo = $logo;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isHomePage(): bool
    {
        return $this->logo->isHomePage();
    }
}
