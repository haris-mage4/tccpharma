<?php

/**
 * Copyright © Eagle All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Eagle\CustomSearchBlock\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SearchBlock extends Template
{
    const SEARCH_TERM_CONFIG = 'search_result/page/ranges';

    protected $_storeManager;

    protected Json $serialize;
    private ScopeConfigInterface $scopeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Json $serialize
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Json $serialize,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->serialize = $serialize;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getParam()
    {
        return $this->_request->getParam('q');
    }

    public function getSearchContentBySearchTerm()
    {
        $searchTermConfig = $this->scopeConfig->getValue(self::SEARCH_TERM_CONFIG, ScopeInterface::SCOPE_STORE);

        if (empty($searchTermConfig)) {
            return null;
        }

        $unserializedData = $this->serialize->unserialize($searchTermConfig);

        foreach ($unserializedData as $row) {
            if (isset($row['search_term']) && $row['search_term'] === $this->getParam()) {
                return $row['content'];
            }
        }

        return null;
    }

}
