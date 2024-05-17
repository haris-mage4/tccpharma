<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Controller\Adminhtml\Mark;

use Magento\Backend\App\Action\Context;
use Magento\TestFramework\ErrorLog\Logger;
use Ulmod\Ordernotes\Model\NotesFactory;
        
/**
 * Notes update controller
 */
class Read extends \Magento\Backend\App\Action
{
    /**
     * @var NotesFactory
     */
    protected $notesFactory;
    
    /**
     * @param Context $context
     * @param NotesFactory $notesFactory
     */
    public function __construct(
        Context $context,
        NotesFactory $notesFactory
    ) {
        $this->notesFactory = $notesFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Ulmod_Ordernotes::notes');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getParams();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
            
         /** @var \Ulmod\Ordernotes\Model\NotesFactory $model */
        $model = $this->notesFactory->create()->getCollection()
            ->addFieldToFilter('order_id', $data['id'])
            ->setOrder('id', 'asc')
            ->getLastItem();

        $model->setNewAdminNoteMarkread('1');

        try {
            $model->save();
            $this->messageManager->addSuccess(
                __('You marked this note as read.')
            );
             $this->_session->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/');
            }
             return $resultRedirect->setPath('sales/order/index');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving the note.')
            );
        }

        return $resultRedirect->setPath('sales/order/index');
    }
}
