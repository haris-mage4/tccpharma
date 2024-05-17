<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magemonkeys\RepresentativeAttr\Model\Customer\Attribute\Source;


/**
 * Customer group attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    
    private $httpContext;

    public $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) 
    {
        $this->httpContext = $httpContext;
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
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
