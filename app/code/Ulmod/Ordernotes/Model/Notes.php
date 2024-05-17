<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\Ordernotes\Model;

/**
 * Notes model
 */
class Notes extends \Magento\Framework\Model\AbstractModel
{
    protected $_eventPrefix = 'noteonorder';
    
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Ulmod\Ordernotes\Model\ResourceModel\Notes::class);
    }

    /**
     * @return void
     */
    public function getNotes($orderId)
    {
        $notesCollection = $this->getCollection()
            ->setOrder('updated_at', 'asc');

        $notesCollection->addFieldToFilter(
            'order_id',
            $orderId
        );

        $notesCollection->getSelect()->joinLeft(
            ['user'=>$notesCollection->getTable('admin_user')],
            'user.user_id=main_table.user_id',
            ['firstname', 'lastname']
        );

        $notesCollection->getSelect()->joinLeft(
            ['customer'=>$notesCollection->getTable('customer_entity')],
            'customer.entity_id=main_table.customer_id',
            ['cfirstname' => 'firstname']
        );

        return $notesCollection;
    }

    /**
     * @return int
     */
    public function getNotesCount($customerId)
    {
        return $this->getNotes($customerId)->count();
    }
}
