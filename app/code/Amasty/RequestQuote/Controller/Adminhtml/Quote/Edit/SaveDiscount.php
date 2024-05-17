<?php
namespace Amasty\RequestQuote\Controller\Adminhtml\Quote\Edit;

use Magento\Backend\App\Action;

class SaveDiscount extends Action
{
    protected $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $discount = $this->getRequest()->getParam('discount');
        $surcharge = $this->getRequest()->getParam('surcharge');
        $quoteId = $this->getRequest()->getParam('quote_id');
        
        $this->updateDataInAmastyQuote($quoteId, $discount, $surcharge);

        $result = $this->resultJsonFactory->create();
        return $result->setData([
            'success' => true,
            'message' => $quoteId
        ]);
    }

    protected function updateDataInAmastyQuote($quoteId, $discount, $surcharge)
    {
        $connection = $this->_objectManager->get(\Magento\Framework\App\ResourceConnection::class)
            ->getConnection();
        
        $tableName = $this->_objectManager->get(\Magento\Framework\App\ResourceConnection::class)
            ->getTableName('amasty_quote');

        $query = "UPDATE $tableName SET discount = :discount, surcharge = :surcharge WHERE quote_id = :quote_id";
        
        $bind = [
            'quote_id' => $quoteId,
            'discount' => $discount,
            'surcharge' => $surcharge
        ];

        $connection->query($query, $bind);
    }
}
