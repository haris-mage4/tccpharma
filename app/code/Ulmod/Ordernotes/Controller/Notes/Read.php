<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Ulmod\Ordernotes\Controller\Notes;

use Magento\Framework\App\Action;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Ulmod\Ordernotes\Model\NotesFactory;
        
/**
 * Read controller
 */
class Read extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CustomerSession
     */
    protected $factory;

    /**
     * @param Context $context
     * @param NotesFactory $factory
     */
    public function __construct(
        Context $context,
        NotesFactory $factory
    ) {
        $this->factory = $factory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);
            
        $post = $this->getRequest()->getPostValue();

        $id = $this->getRequest()->getParam('id');
        $orderId = $this->getRequest()->getParam('order_id');
        $data = $this->getRequest()->getParams();
        
        /** @var \Ulmod\Ordernotes\Model\Notes $model */
        $model = $this->factory->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('id', 'asc')
            ->getLastItem();

        $model->setNewCustomerNoteMarkread(1);

        try {
            $model->save();

        } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t process your request right now. Sorry.')
                );
        }

        return $resultRedirect->setPath('ordernotes/notes/index/order_id/'  . $orderId);
    }
}
