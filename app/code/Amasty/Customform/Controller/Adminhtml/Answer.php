<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml;

use Amasty\Customform\Model\AnswerRepository;
use Amasty\Customform\Model\FormRegistry;
use Amasty\Customform\Model\Grid\Bookmark;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

abstract class Answer extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Customform::data';

    /**
     * @var AnswerRepository
     */
    protected $answerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Bookmark
     */
    protected $bookmark;

    /**
     * @var FormRegistry
     */
    protected $formRegistry;

    public function __construct(
        Context $context,
        AnswerRepository $answerRepository,
        FormRegistry $formRegistry,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        Bookmark $bookmark
    ) {
        $this->answerRepository = $answerRepository;
        $this->logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
        $this->bookmark = $bookmark;
        $this->formRegistry = $formRegistry;

        parent::__construct($context);
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE)
            ->_addBreadcrumb(__('Amasty: Custom Forms'), __('Submitted Data'));

        return $this;
    }
}
