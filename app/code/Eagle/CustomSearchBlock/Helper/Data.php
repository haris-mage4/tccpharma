<?php

namespace Eagle\CustomSearchBlock\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Cms\Model\PageFactory as CmsPageFactory;

class Data extends AbstractHelper
{
    protected CmsPageFactory $cmsPageFactory;

    /**
     * @param Context $context
     * @param CmsPageFactory $cmsPageFactory
     */
    public function __construct(
        Context $context,
        CmsPageFactory $cmsPageFactory
    ) {
        parent::__construct($context);
        $this->cmsPageFactory = $cmsPageFactory;
    }

    public function getCmsPageContentById($cmsPageId): string
    {
        $cmsPage = $this->cmsPageFactory->create()->load($cmsPageId);
        return $cmsPage->getContent();
    }

    public function extractNamesFromHtml(): array
    {
        $names = [];

        $dom = new \DOMDocument;
        $this->loadHTMLWithErrors($dom, $this->getCmsPageContentById(19));
        $rows = $dom->getElementsByTagName('tr');

        foreach ($rows as $row) {
            $nameElement = $row->getElementsByTagName('td')->item(1);

            if ($nameElement) {
                $names[] = $nameElement->textContent;
            }
        }

        return $names;
    }


    /**
     * Check if any word in the value obtained from getParam matches any word in the array of names.
     *
     * @return bool
     */
    public function isParamInNames(): bool
    {
        $paramValue = $this->getParam();
        $names = $this->extractNamesFromHtml();
        $paramWords = explode(' ', strtolower($paramValue));

        foreach ($names as $name) {
            $nameWords = explode(' ', strtolower($name));
            if (array_intersect($paramWords, $nameWords)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if any word in the value obtained from getParam matches any word in the array of names.
     *
     * @param $paramValue
     * @return bool
     */
    public function isParamInSearch($paramValue): bool
    {
        $names = $this->extractNamesFromHtml();
        $paramWords = explode(' ', strtolower($paramValue));

        foreach ($names as $name) {
            $nameWords = explode(' ', strtolower($name));
            if (array_intersect($paramWords, $nameWords)) {
                return true;
            }
        }

        return false;
    }

    public function extractTextContentFromHtmlForMediacalPage(): string
    {
        $textContent = '';

        $dom = new \DOMDocument;
        $this->loadHTMLWithErrors($dom, $this->getCmsPageContentById(24));

        // Get all text nodes
        $textNodes = $dom->getElementsByTagName('body')->item(0)->childNodes;

        foreach ($textNodes as $node) {
            // Extract text content from each node and concatenate
            $textContent .= $node->textContent . ' ';
        }

        return $textContent;
    }

    public function isParamInMediacalPage(): bool
    {
        $paramValue = $this->getParam();
        $textContent = '';

        $dom = new \DOMDocument;
        $this->loadHTMLWithErrors($dom, $this->getCmsPageContentById(24));

        // Extract text content from all text nodes
        $xpath = new \DOMXPath($dom);
        $textNodes = $xpath->query('//text()');

        foreach ($textNodes as $textNode) {
            // Concatenate text content with space separator
            $textContent .= trim($textNode->nodeValue) . ' ';
        }

        // Prepare the regular expression pattern to include word boundaries and digits
        $pattern = '/\b(' . preg_quote($paramValue, '/') . ')\b/i';

        // Check if the pattern matches in the text content
        if (preg_match($pattern, $textContent)) {
            return true;
        }

        return false;
    }


    /**
     * Check if any word or number in the value obtained from getParam matches any word or number in the text content of the medical page.
     *
     * @param string $paramValue
     * @return bool
     */
    public function isParamInSearchForMediacalPage(string $paramValue): bool
    {
        $textContent = '';

        $dom = new \DOMDocument;
        $this->loadHTMLWithErrors($dom, $this->getCmsPageContentById(24));

        // Extract text content from all text nodes
        $xpath = new \DOMXPath($dom);
        $textNodes = $xpath->query('//text()');

        foreach ($textNodes as $textNode) {
            // Concatenate text content with space separator
            $textContent .= trim($textNode->nodeValue) . ' ';
        }

        // Prepare the regular expression pattern to include word boundaries and digits
        $pattern = '/\b(' . preg_quote($paramValue, '/') . ')\b/i';

        // Check if the pattern matches in the text content
        if (preg_match($pattern, $textContent)) {
            return true;
        }

        return false;
    }

    /**
     * Load HTML content into a DOMDocument instance with error handling.
     *
     * @param \DOMDocument $dom
     * @param string $html
     * @return void
     */
    private function loadHTMLWithErrors(\DOMDocument $dom, string $html): void
    {
        // Suppress warnings and errors
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
    }

    /**
     * @return string
     */
    public function getParam()
    {
        return $this->_request->getParam('q');
    }
}
