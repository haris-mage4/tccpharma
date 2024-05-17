<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Request\QuoteEntity;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use Amasty\QuoteAttributes\Model\Metadata\Form;
use Amasty\QuoteAttributes\Model\Metadata\FormFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Used for retrieve quote_entity attributes from request & populate model with validated values.
 */
class UpdateData
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(FormFactory $formFactory, RequestInterface $request)
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
    }

    /**
     * @param $quoteEntity
     * @param string $scope
     * @param bool $isAjax
     * @return void
     * @throws ValidatorException
     */
    public function execute($quoteEntity, string $scope, bool $isAjax): void
    {
        /** @var Form $form */
        $form = $this->formFactory->create([
            'quoteEntity' => $quoteEntity,
            'isAjaxRequest' => $isAjax
        ]);
        $data = $form->extractData($this->request, $scope);
        if ($errors = $form->validateData($data)) {
            throw new ValidatorException(null, null, $errors);
        }
        $form->compactData($data);
    }
}
