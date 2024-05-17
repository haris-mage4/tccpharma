<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\Repository;

use Amasty\QuoteAttributes\Api\AttributeRepositoryInterface;
use Amasty\QuoteAttributes\Api\Data\AttributeInterface;
use Amasty\QuoteAttributes\Model\Attribute\Command\DeleteByIdInterface;
use Amasty\QuoteAttributes\Model\Attribute\Command\DeleteInterface;
use Amasty\QuoteAttributes\Model\Attribute\Command\SaveInterface;
use Amasty\QuoteAttributes\Model\Attribute\Query\GetByCodeInterface;
use Amasty\QuoteAttributes\Model\Attribute\Query\GetByIdInterface;
use Amasty\QuoteAttributes\Model\Attribute\Query\GetListInterface;
use Amasty\QuoteAttributes\Model\Attribute\Query\GetNewInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class AttributeRepository implements AttributeRepositoryInterface
{
    /**
     * @var GetNewInterface
     */
    private $getNew;

    /**
     * @var GetByCodeInterface
     */
    private $getByCode;

    /**
     * @var GetByIdInterface
     */
    private $getById;

    /**
     * @var SaveInterface
     */
    private $saveCommand;

    /**
     * @var DeleteInterface
     */
    private $deleteCommand;

    /**
     * @var DeleteByIdInterface
     */
    private $deleteByIdCommand;

    /**
     * @var GetListInterface
     */
    private $getList;

    public function __construct(
        GetNewInterface $getNew,
        GetByCodeInterface $getByCode,
        GetByIdInterface $getById,
        SaveInterface $saveCommand,
        DeleteInterface $deleteCommand,
        DeleteByIdInterface $deleteByIdCommand,
        GetListInterface $getList
    ) {
        $this->getNew = $getNew;
        $this->getByCode = $getByCode;
        $this->getById = $getById;
        $this->saveCommand = $saveCommand;
        $this->deleteCommand = $deleteCommand;
        $this->deleteByIdCommand = $deleteByIdCommand;
        $this->getList = $getList;
    }

    public function getNew(): AttributeInterface
    {
        return $this->getNew->execute();
    }

    public function get(string $attributeCode): AttributeInterface
    {
        return $this->getByCode->execute($attributeCode);
    }

    public function getById(int $id): AttributeInterface
    {
        return $this->getById->execute($id);
    }

    public function getList(SearchCriteriaInterface $searchCriteria): AttributeSearchResultsInterface
    {
        return $this->getList->execute($searchCriteria);
    }

    public function save(AttributeInterface $attribute): void
    {
        $this->saveCommand->execute($attribute);
    }

    public function delete(AttributeInterface $attribute): void
    {
        $this->deleteCommand->execute($attribute);
    }

    public function deleteById(int $id): void
    {
        $this->deleteByIdCommand->execute($id);
    }
}
