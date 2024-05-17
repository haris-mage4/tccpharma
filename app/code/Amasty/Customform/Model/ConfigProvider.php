<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    const PATH_PREFIX = 'amasty_customform/';
    const DEFAULT_DATEFORMAT = 'mm/dd/yy';

    protected $pathPrefix = self::PATH_PREFIX;

    const XML_PATH_DATE_FORMAT = 'advanced/date_format';
    const XML_PATH_EMAIL_SENDER = 'email/sender_email_identity';
    const XML_PATH_EMAIL_RECIPIENTS = 'email/recipient_email';
    const XML_PATH_GOOGLE_KEY = 'advanced/google_key';
    const XML_PATH_GDPR_TEXT = 'gdpr/text';

    public function getModuleConfig(string $path, ?string $scopeCode = null, ?int $scopeId = null): ?string
    {
        $scopeCode = $scopeCode ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        return $this->getValue($path, $scopeId, $scopeCode);
    }

    public function getDateFormat(): string
    {
        return (string) $this->getModuleConfig(self::XML_PATH_DATE_FORMAT) ?: self::DEFAULT_DATEFORMAT;
    }

    public function getEmailSender(): string
    {
        return (string) $this->getModuleConfig(self::XML_PATH_EMAIL_SENDER);
    }

    public function getRecipientEmails(): array
    {
        $config = (string) $this->getModuleConfig(self::XML_PATH_EMAIL_RECIPIENTS);
        $emails = array_map('trim', explode(',', $config));

        return array_filter($emails, function (string $email): bool {
            return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
        });
    }

    public function getGoogleKey(): string
    {
        return (string) $this->getModuleConfig(self::XML_PATH_GOOGLE_KEY);
    }

    public function getGdprText(?int $storeId = null): string
    {
        return (string) $this->getModuleConfig(
            self::XML_PATH_GDPR_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
