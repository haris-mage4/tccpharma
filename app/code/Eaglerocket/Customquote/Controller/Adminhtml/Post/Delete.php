<?php


namespace Eaglerocket\Customquote\Controller\Adminhtml\Post;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;


class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\Grid\Model\GridFactory
     */
    var $postFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\Grid\Model\GridFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Eaglerocket\Customquote\Model\PostFactory $postFactory
    ) 
    {
        parent::__construct($context);
        $this->postFactory = $postFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {        

        $data = $this->getRequest()->getParam('id');
        if (!$data) {
            $this->_redirect('eaglerocket_customquote/post/index');
            return;
        }
        try {              
$rowData = $this->postFactory->create();
$rowData->load($data);// id is the deleted post id
$rowData->delete();

            // $rowData = $this->postFactory->create();

            // $rowData->setData($data);
           
            // $rowData->delete();
            $this->messageManager->addSuccess(__('Row data has been successfully deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('eaglerocket_customquote/post/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Eaglerocket_Customquote::delete');
    }
}