<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Plugin\RequestQuote\Model\Quote\Backend\Edit;

use Amasty\QuoteAttributes\Block\Adminhtml\Quote\Edit\Attributes;
use Amasty\QuoteAttributes\Model\Request\QuoteEntity\UpdateData;
use Amasty\RequestQuote\Model\Quote\Backend\Edit;
use Amasty\RequestQuote\Model\Quote\Backend\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Pass quote attributes values to quote entity.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ImportData
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UpdateData
     */
    private $updateData;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(UpdateData $updateData, Session $session, RequestInterface $request)
    {
        $this->session = $session;
        $this->updateData = $updateData;
        $this->request = $request;
    }

    /**
     * Extract attributes for Quote Entity from HttpRequest.
     *
     * @param Edit $subject
     * @return Edit
     * @throws ValidatorException
     *
     * @see Edit::importPostData
     */
    public function afterImportPostData(Edit $subject): Edit
    {
        if ($this->request->getParam(Attributes::QUOTE_ENTITY_SCOPE)) {
            $editedQuote = $subject->getQuote();
            // if quote has parent id that mean $editedQuote - not amasty quote and dont have quote entity
            $originalQuote = $this->session->getParentId($editedQuote->getId())
                ? $this->session->getParentQuote()
                : $editedQuote;

            if ($quoteEntity = $originalQuote->getExtensionAttributes()->getQuoteEntity()) {
                $this->updateData->execute($quoteEntity, Attributes::QUOTE_ENTITY_SCOPE, false);
            }
        }

        return $subject;
    }
}
