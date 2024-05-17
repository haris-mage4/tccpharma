<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Block\Adminhtml\Quote\Edit;

use Amasty\QuoteAttributes\Block\Data\Form\Element\Boolean;
use Amasty\QuoteAttributes\Model\QuoteEntity\GetAttributeList;
use Amasty\QuoteAttributes\Model\Source\Attribute\FrontendInput;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Block\Adminhtml\Quote\Edit\Tab\Info;
use Amasty\RequestQuote\Model\Quote\Backend\Session as BackendSession;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic as FormGeneric;
use Magento\Customer\Model\Customer;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Customer\Model\CustomerFactory;

class Attributes extends FormGeneric
{
    public const QUOTE_ENTITY_SCOPE = 'quote_entity';

    /**
     * @var CustomerFactory
     */
    private CustomerFactory $_customerFactory;

    /**
     * @var GetAttributeList
     */
    private GetAttributeList $getAttributeList;

    /**
     * @var QuoteInterface|null
     */
    private  $quote;

    /**
     * @var BackendSession
     */
    private BackendSession $backendSession;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        BackendSession $backendSession,
        GetAttributeList $getAttributeList,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->getAttributeList = $getAttributeList;
        $this->backendSession = $backendSession;
        $this->_customerFactory = $customerFactory;
    }

    protected function _prepareForm(): Attributes
    {
        if ($this->getQuote() && $quoteEntity = $this->getQuote()->getExtensionAttributes()->getQuoteEntity()) {
            $form = $this->_formFactory->create(
                [
                    'data' => [
                        'id' => 'edit_form',
                        'action' => $this->getData('action'),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
                ]
            );

            $form->setUseContainer(false);
            $form->setFieldNameSuffix(self::QUOTE_ENTITY_SCOPE);

            $fieldset = $form->addFieldset('base_fieldset', [
                'collapsable' => false
            ]);

            $this->_setFieldset($this->getAttributeList->execute($quoteEntity), $fieldset);

            $this->setForm($form);
        }

        return parent::_prepareForm();
    }

    /**
     * @return Attributes
     */
    protected function _initFormValues(): Attributes
    {
        $quote = $this->getQuote();
        if ($quote && ($quoteEntity = $quote->getExtensionAttributes()->getQuoteEntity())) {
            $customerId = $quote->getCustomer()->getId();
            $customer = $this->_customerFactory->create()->load($customerId);
            $form = $this->getForm();

            foreach ($this->getAttributeList->execute($quoteEntity) as $attribute) {
                $value = $this->getAttributeValue($attribute, $customer, $quoteEntity);
                $form->getElement($attribute->getAttributeCode())->setValue($value);
            }
        }

        return parent::_initFormValues();
    }

    /**
     * Get the value for the given attribute based on business logic.
     *
     * @param $attribute
     * @param Customer $customer
     * @param $quoteEntity
     * @return mixed
     */
    private function getAttributeValue($attribute, Customer $customer, $quoteEntity)
    {
        $attributeCode = $attribute->getAttributeCode();

        switch ($attributeCode) {
            case "facility":
                $value = $customer->getData('facility_name');
                break;

            case "select_sales_rep":
                $value = $this->getSalesRepresentativeValue($customer);
                break;

            default:
                $value = $quoteEntity->hasData($attributeCode)
                    ? $quoteEntity->getData($attributeCode)
                    : $attribute->getDefaultValue();
                break;
        }

        return $value;
    }

    /**
     * Get the value for the "select_sales_rep" attribute based on business logic.
     *
     * @param Customer $customer
     * @return mixed
     */
    private function getSalesRepresentativeValue(Customer $customer)
    {
        $salesRepMapping = [
            '41' => '18',
            '40' => '19',
            '54' => '42',
            '27' => '17',
            '35' => '16',
            '29' => '15',
            '28' => '14',
            '32' => '13',
        ];

        $defaultSalesRep = $customer->getData('sales_representative');
        return $salesRepMapping[$defaultSalesRep] ?? $defaultSalesRep;
    }

    /**
     * @return QuoteInterface|null
     */
    private function getQuote(): ?QuoteInterface
    {
        if ($this->quote === null && $this->getParentBlock()) {
            $parentBlock = $this->getParentBlock();
            if ($parentBlock instanceof Info) {
                $this->quote = $parentBlock->getSource();
            } else {
                $this->quote = $this->backendSession->getQuote();
            }
        }

        return $this->quote;
    }

    /**
     * @return string[]
     */
    protected function _getAdditionalElementTypes(): array
    {
        return [
            FrontendInput::BOOLEAN => Boolean::class
        ];
    }
}
