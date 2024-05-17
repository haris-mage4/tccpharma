<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Metadata;

use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Model\Attribute;
use Amasty\QuoteAttributes\Model\Attribute\Data\Multiselect;
use Amasty\QuoteAttributes\Model\QuoteEntity;
use Amasty\QuoteAttributes\Model\QuoteEntity\GetAttributeList;
use Amasty\QuoteAttributes\Model\ResourceModel\QuoteEntity as QuoteEntityResource;
use Magento\Eav\Model\Attribute\Data\AbstractData;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Validator\Factory as ValidatorFactory;

class Form
{
    /**
     * @var AttributeDataFactory
     */
    private $attributeDataFactory;

    /**
     * @var QuoteEntityInterface
     */
    private $quoteEntity;

    /**
     * @var bool
     */
    private $isAjaxRequest;

    /**
     * @var GetAttributeList
     */
    private $getAttributeList;

    /**
     * @var null|AttributeInterface
     */
    private $attributes;

    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    public function __construct(
        GetAttributeList $getAttributeList,
        AttributeDataFactory $attributeDataFactory,
        ValidatorFactory $validatorFactory,
        QuoteEntityInterface $quoteEntity,
        bool $isAjaxRequest
    ) {
        $this->attributeDataFactory = $attributeDataFactory;
        $this->getAttributeList = $getAttributeList;
        $this->validatorFactory = $validatorFactory;
        $this->quoteEntity = $quoteEntity;
        $this->isAjaxRequest = $isAjaxRequest;
    }

    /**
     * @return QuoteEntityInterface|QuoteEntity
     */
    public function getQuoteEntity(): QuoteEntityInterface
    {
        return $this->quoteEntity;
    }

    /**
     * @return bool
     */
    public function isAjaxRequest(): bool
    {
        return $this->isAjaxRequest;
    }

    /**
     * Extract data from request and return associative data array
     *
     * @param RequestInterface $request
     * @param string|null $scope the request scope
     * @param bool $scopeOnly search value only in scope or search value in global too
     * @return array
     */
    public function extractData(RequestInterface $request, ?string $scope = null, bool $scopeOnly = true): array
    {
        $data = [];
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->getAttributeDataModel($attribute);
            $dataModel->setRequestScope($scope);
            $dataModel->setRequestScopeOnly($scopeOnly);
            $data[$attribute->getAttributeCode()] = $dataModel->extractValue($request);
        }

        return $data;
    }

    /**
     * Compact data array to current entity
     *
     * @param array $data
     * @return void
     */
    public function compactData(array $data): void
    {
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            $attributeCode = $attribute->getAttributeCode();

            if (isset($data[$attributeCode])) {
                $dataModel->getEntity()->setData($attributeCode, $data[$attributeCode]);
            }
        }
    }

    /**
     * Return array of entity formatted values
     *
     * @param string $format
     * @param int $storeId
     * @return array
     */
    public function outputData(string $format = AttributeDataFactory::OUTPUT_FORMAT_TEXT, ?int $storeId = null): array
    {
        $data = [];
        /** @var $attribute \Magento\Eav\Model\Attribute */
        foreach ($this->getAllowedAttributes() as $attribute) {
            if (!$this->getQuoteEntity()->hasData($attribute->getAttributeCode())) {
                $this->getQuoteEntity()->setData($attribute->getAttributeCode(), '');
            }
            $attribute->setStoreId($storeId);
            $dataModel = $this->getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            $data[$attribute->getAttributeCode()] = $dataModel->outputValue($format);
        }

        return $data;
    }

    /**
     * Validate given data for input validation.
     * Retyrn array of errors. Empty array mean is $data valid.
     *
     * @param array $data
     * @return array
     */
    public function validateData(array $data): array
    {
        if ($this->getAllowedAttributes()) {
            $validator = $this->validatorFactory->createValidator(QuoteEntityResource::TYPE_CODE, 'form', [
                'eav_data_validator' => [
                    ['method' => 'setAttributes', 'arguments' => [$this->getAllowedAttributes()]],
                    ['method' => 'setData', 'arguments' => [$data]]
                ]
            ]);
            $validator->isValid($this->getQuoteEntity());
            $messages = $validator->getMessages();
        } else {
            $messages = [];
        }

        return $messages;
    }

    /**
     * @return AttributeInterface[]
     */
    public function getAllowedAttributes(): array
    {
        return $this->getAttributes();
    }

    /**
     * @param string $attributeCode
     * @return AttributeInterface|null
     */
    public function getAttribute(string $attributeCode): ?AttributeInterface
    {
        $attributes = $this->getAttributes();
        return $attributes[$attributeCode] ?? null;
    }

    /**
     * @return AttributeInterface[]
     */
    private function getAttributes(): array
    {
        if ($this->attributes === null) {
            $attributes = $this->getAttributeList->execute($this->getQuoteEntity());

            $this->attributes = [];
            foreach ($attributes as $attribute) {
                $this->attributes[$attribute->getAttributeCode()] = $attribute;
                if ($attribute->getFrontendInput() === 'multiselect') {
                    // compatibility with attributes created before data model added
                    $attribute->setDataModel(Multiselect::class);
                }
            }
        }

        return $this->attributes;
    }

    /**
     * @param AttributeInterface|Attribute $attribute
     * @return AbstractData
     */
    private function getAttributeDataModel(AttributeInterface $attribute): AbstractData
    {
        $dataModel = $this->attributeDataFactory->create($attribute, $this->getQuoteEntity());
        $dataModel->setIsAjaxRequest($this->isAjaxRequest());

        return $dataModel;
    }
}
