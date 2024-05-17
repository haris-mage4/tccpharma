<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Observer\Form;

use Amasty\Customform\Api\Data\AnswerInterface;
use Amasty\Customform\Api\Data\FormInterface;
use Amasty\Customform\Model\Form;
use Amasty\Customform\Model\Mail\Notification;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class Submit implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Notification
     */
    private $notification;

    public function __construct(
        ManagerInterface $messageManager,
        Notification $notification
    ) {
        $this->messageManager = $messageManager;
        $this->notification = $notification;
    }

    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        $answer = $event->getAnswer();
        $form = $event->getForm();

        if ($form instanceof FormInterface && $answer instanceof AnswerInterface) {
            $this->notification->sendNotifications($form, $answer);
            $this->showSuccessMessage($form);
        }
    }

    private function showSuccessMessage(Form $formModel): void
    {
        $message = $formModel->getSuccessMessage();

        if ($message) {
            $this->messageManager->addSuccessMessage(
                $message
            );
        }
    }
}
