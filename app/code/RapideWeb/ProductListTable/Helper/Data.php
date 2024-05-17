<?php
namespace RapideWeb\ProductListTable\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    /**
     * @var Registry
     */
    protected Registry $_coreRegistry;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $_scopeConfig;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Get Enabled/Disabled Extension Value
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->_scopeConfig->getValue('productlisttable/general/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Product Listing Table Page Url
     * @return string
     */
    public function getPageTitle(): string
    {
        return $this->_scopeConfig->getValue('productlisttable/general/page_title', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Product Listing Table Page Url
     * @return string
     */
    public function isShowNavigation(): string
    {
        return $this->_scopeConfig->getValue('productlisttable/general/navigation', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Show/Hide Product Image Value
     * @return boolean
     */
    public function isShowProductImage(): bool
    {
        return $this->_scopeConfig->getValue('productlisttable/general/product_image', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Categories
     *
     */
    public function getCategories(){
        return $this->_scopeConfig->getValue('productlisttable/general/category', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Show/Hide Product Option Value
     * @return boolean
     */
    public function isShowProductOption(): bool
    {
        return $this->_scopeConfig->getValue('productlisttable/general/product_option', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getImageWidth(): string
    {
        return $this->_scopeConfig->getValue('productlisttable/design/image_width', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getImageHeight(): string
    {
        return $this->_scopeConfig->getValue('productlisttable/design/image_height', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Add to Cart button Text
     * @return string
     */
    public function getButtonText(): string
    {
        return $this->_scopeConfig->getValue('productlisttable/design/button_text', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Add to Cart button Background color
     * @return string
     */
    public function getButtonBgColor(): string
    {
        return $this->_scopeConfig->getValue('productlisttable/design/button_bg_color', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Add to Cart button Text color
     * @return string
     */
    public function getButtonColor(): string
    {
        return $this->_scopeConfig->getValue('productlisttable/design/button_color', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Set Query Character
     * @param $char
     */
    public function setQueryChar($char)
    {
        $this->_coreRegistry->register('queryChar', $char);
    }

    /**
     * Get Query Character
     */
    public function getQueryChar()
    {
        return $this->_coreRegistry->registry('queryChar');
    }
}
