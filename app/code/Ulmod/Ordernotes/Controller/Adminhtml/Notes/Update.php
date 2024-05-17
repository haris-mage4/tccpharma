<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\Ordernotes\Controller\Adminhtml\Notes;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Ulmod\Ordernotes\Model\NotesFactory;
use Magento\Backend\Model\Auth\Session as BackendAuthSession;
        
/**
 * Notes update controller
 */
class Update extends \Ulmod\Ordernotes\Controller\Adminhtml\Notes
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var BackendAuthSession
     */
    protected $backendAuthSession;

    /**
     * @var NotesFactory
     */
    protected $notesFactory;
    
    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param NotesFactory $notesFactory
     * @param BackendAuthSession $backendAuthSession
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        NotesFactory $notesFactory,
        BackendAuthSession $backendAuthSession
    ) {
        $this->notesFactory = $notesFactory;
        $this->backendAuthSession = $backendAuthSession;
        parent::__construct(
            $context,
            $coreRegistry,
            $resultForwardFactory,
            $resultPageFactory
        );
    }
    
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        // Edit note
        if (isset($params['masonotes_note']) && count($params['masonotes_note']) > 0) {
            foreach ($params['masonotes_note'] as $noteId => $exist) {
                $note = $this->notesFactory->create()
                    ->load($noteId);
                if (isset($exist['delete'])) {
                    $note->delete();
                } else {
                    $note->setNote($exist['note'])
                        ->setOrderId($id)
                        ->setVisible($exist['status'])
                        ->save();
                }
            }
        }

        // New note
        $sessionId = $this->backendAuthSession->getUser()->getId();
        if ($params['masonotes_new'][0]['note'] != '') {
            $this->notesFactory->create()
                ->setNote($params['masonotes_new'][0]['note'])
                ->setOrderId($id)
                ->setUserId($sessionId)
                ->setVisible($params['masonotes_new'][0]['status'])
                ->setNotify($params['masonotes_new'][0]['email'])
                ->setNewAdminNote('1')
                ->setNewCustomerNote('0')
                ->save();
        }
        
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
