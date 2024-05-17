<?php

namespace Magemonkeys\RepresentativeAttr\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    public $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) 
    {
        parent::__construct($context);
        $this->httpContext = $httpContext;
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfigValue(){
        $value = $this->scopeConfig->getValue(
            'Magemonkeys_RepresentativeAttr/general/representative',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
        
        $arr = explode(",",$value);
        $options[] = ['label' => 'Please select Representative.', 'value' => ''];

          foreach($arr as $val){

                $options[] = [
                            'label' => $val,
                            'value' => $val
                        ];

            }
        // echo "<pre>"; print_r($options); die();
        return $options;

    }

}