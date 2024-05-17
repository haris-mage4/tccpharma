<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Controller\Adminhtml\Forms;

use Amasty\Customform\Api\Data\FormInterface;
use Amasty\Customform\Model\Form;
use Amasty\Customform\Model\Form\Save\Preparation\PreparationInterface;
use Amasty\Customform\Model\FormFactory;
use Amasty\Customform\Model\FormRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;

class Save extends Action
{
    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var FormRepository
     */
    private $formRepository;

    /**
     * @var PreparationInterface
     */
    private $formDataPreparationProcessor;

    public function __construct(
        ActionContext $context,
        ValidatorFactory $validatorFactory,
        FormFactory $formFactory,
        FormRepository $formRepository,
        PreparationInterface $formDataPreparationProcessor
    ) {
        parent::__construct($context);

        $this->validatorFactory = $validatorFactory;
        $this->formFactory = $formFactory;
        $this->formRepository = $formRepository;
        $this->formDataPreparationProcessor = $formDataPreparationProcessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Customform::page');
    }

    public function validate($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml'])) {
            /** @var $validatorCustomLayout \Magento\Framework\View\Model\Layout\Update\Validator */
            $validatorCustomLayout = $this->validatorFactory->create();
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        }
        return $errorNo;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $id = $data[FormInterface::FORM_ID] ?? 0;

        if ($data) {
            /** @var Form $model */
            $model = $this->formFactory->create();

            try {
                if ($id) {
                    $model = $this->formRepository->get($id);
                }

                $data = $this->formDataPreparationProcessor->prepare($data);
                $model->setData($data);
                $session = $this->_getSession();
                $session->setAmCustomFormData($data);
                $this->validateFormCode($model);
                $this->formRepository->save($model);
                $session->unsAmCustomFormData();
                $this->messageManager->addSuccessMessage(__('You have saved this form.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['form_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }

            return $resultRedirect->setPath('*/*/edit', ['form_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param Form $formModel
     * @throws LocalizedException
     */
    protected function validateFormCode(Form $formModel)
    {
        $exist = false;
        if ($formModel->getCode()) {
            $model = $this->formRepository->getByFormCode($formModel->getCode());

            if ($model && $model->getFormId()) {
                if ($formModel->getFormId() && ($model->getFormId() != $formModel->getFormId())) {
                    $exist = true;
                }

                if (!$formModel->getFormId()) {
                    $exist = true;
                }
            }

            if ($exist) {
                throw new LocalizedException(__('Form with code %1 already exists.', $formModel->getCode()));
            }
        } else {
            throw new LocalizedException(__('Form code was not found'));
        }
    }
}
