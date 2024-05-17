<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Form\Rendering\Autocomplete\VariablesValue;

use Amasty\Customform\Helper\Messages;
use Amasty\Customform\Model\Utils\CustomerInfo;
use Magento\Customer\Model\Customer;

class CustomerFieldsProvider implements ProviderInterface
{
    const CUSTOMER_FIELDS = [
        Messages::FIRST_NAME,
        Messages::LAST_NAME,
        Messages::CITY,
        Messages::COMPANY,
        Messages::EMAIL,
        Messages::PHONE_NUMBER,
        Messages::POST_CODE,
        Messages::REGION,
        Messages::STREET_ADDRESS,
        Messages::FACILITY_NAME
    ];

    /**
     * @var string[]
     */
    private $acceptableVariables;

    /**
     * @var CustomerInfo
     */
    private $customerInfo;

    public function __construct(
        CustomerInfo $customerInfo,
        array $acceptableVariables = self::CUSTOMER_FIELDS
    ) {
        $this->acceptableVariables = array_map(function (string $variable) {
            return trim($variable, '{}');
        }, $acceptableVariables);
        $this->customerInfo = $customerInfo;
    }

    public function isCanRetrieve(string $variableName): bool
    {
        return in_array($variableName, $this->acceptableVariables) && $this->customerInfo->isLoggedIn();
    }

    public function getValue(string $variableName): string
    {
        return $this->getCustomerValue(
            $this->customerInfo->getCurrentCustomer(),
            $variableName
        );
    }

    public function getCustomerValue(Customer $customer, string $fieldName): string
    {
        $variable = sprintf('{%s}', $fieldName);
        $addressFields = [
            Messages::COMPANY,
            Messages::CITY,
            Messages::POST_CODE,
            Messages::REGION,
            Messages::STREET_ADDRESS,
            Messages::PHONE_NUMBER,
        ];
        $value = in_array($variable, $addressFields)
            ? $this->getAddressValue($customer, $variable, $fieldName)
            : (string) $customer->getData($fieldName);

        return $value ?: '';
    }

    private function getAddressValue(Customer $customer, string $fieldName, string $fieldCode): string
    {
        $address = $customer->getDefaultBillingAddress();

        if (!$address) {
            return '';
        }

        switch ($fieldName) {
            case Messages::STREET_ADDRESS:
                $value = preg_replace('/[\n]/', ' - ', $address->getData($fieldCode));
                break;
            case Messages::PHONE_NUMBER:
                $value = preg_replace("/[^0-9]/", "", $address->getData($fieldCode));
                break;
            default:
                $value = $address->getData($fieldCode);
        }

        return (string) $value;
    }
}
