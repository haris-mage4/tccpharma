<?php


namespace Eaglerocket\Customquote\Controller\Adminhtml\Post;

class Save extends \Magento\Backend\App\Action
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
    ) {
        parent::__construct($context);
        $this->postFactory = $postFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('customquote/post/addrow');
            return;
        }
        try {
            $rowData = $this->postFactory->create();
                  //  $rowData->addData($data)->save();

            $rowData->setData($data);
            // if (isset($data['id'])) {
            //     $rowData->setEntityId($data['id']);
            // }
            $rowData->save();
            $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
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
        return $this->_authorization->isAllowed('Eaglerocket_Customquote::save');
    }
}