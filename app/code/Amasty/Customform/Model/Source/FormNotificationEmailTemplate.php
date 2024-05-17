<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FormNotificationEmailTemplate implements OptionSourceInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
     */
    private $emailTemplateSource;

    public function __construct(
        \Magento\Config\Model\Config\Source\Email\Template $emailTemplateSource
    ) {
        $this->emailTemplateSource = $emailTemplateSource;
    }

    public function toOptionArray()
    {
        return $this->emailTemplateSource->setPath('amasty/customform/email/template')->toOptionArray();
    }
}
