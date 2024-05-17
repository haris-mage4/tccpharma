<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\ResourceModel;

use Amasty\Customform\Api\Data\AnswerInterface;
use Amasty\Customform\Model\ResourceModel\Answer\CRUDCallbacks\CallbackInterface;
use Amasty\Customform\Model\ResourceModel\Answer\CRUDCallbacks\CallbackPool as BeforeSaveCallbacksPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Answer extends AbstractDb
{
    const TABLE_NAME = 'amasty_customform_answer';

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var BeforeSaveCallbacksPool
     */
    private $beforeSaveCallbacks;

    public function __construct(
        Context $context,
        TimezoneInterface $timezone,
        BeforeSaveCallbacksPool $beforeSaveCallbacks,
        $connectionName = null
    ) {
        $this->timezone = $timezone;

        parent::__construct(
            $context,
            $connectionName
        );
        $this->beforeSaveCallbacks = $beforeSaveCallbacks;
    }

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, AnswerInterface::ANSWER_ID);
    }

    public function save(AbstractModel $object)
    {
        if ($this->isObjectNotNew($object) && !$object->getData(AnswerInterface::UPDATED_AT)) {
            $now = $this->timezone->date();
            $object->setData(AnswerInterface::UPDATED_AT, $this->timezone->convertConfigTimeToUtc($now));
        }

        /** @var CallbackInterface $beforeSaveCallback **/
        /** @var AnswerInterface $object **/
        foreach ($this->beforeSaveCallbacks as $beforeSaveCallback) {
            $beforeSaveCallback->process($object);
        }

        return parent::save($object);
    }
}
