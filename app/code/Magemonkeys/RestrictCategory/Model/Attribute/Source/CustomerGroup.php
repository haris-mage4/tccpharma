<?php

namespace Magemonkeys\RestrictCategory\Model\Attribute\Source;

class CustomerGroup extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements \Magento\Framework\Option\ArrayInterface
{
    const NOT_LOGGED_KEY = '00';
    // const DISABLED_GROUP_KEY = -1;

 /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Group
     */
    private $groupSource;

    public function __construct(\Magento\Customer\Model\Customer\Attribute\Source\Group $groupSource)
    {
        $this->groupSource = $groupSource;
    }

    public function toOptionArray()
    {
        return array_merge(
            [
                /*[
                    'value' => self::DISABLED_GROUP_KEY,
                    'label' => __('NONE')
                ],*/
                [
                    'value' => self::NOT_LOGGED_KEY,
                    'label' => __('NOT LOGGED IN')
                ]
            ],
            $this->groupSource->getAllOptions()
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $optionArray = $this->toOptionArray();
        $labels =  array_column($optionArray, 'label');
        $values =  array_column($optionArray, 'value');
        return array_combine($values, $labels);
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
