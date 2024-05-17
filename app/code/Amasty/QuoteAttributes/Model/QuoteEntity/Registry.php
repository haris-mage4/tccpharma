<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

namespace Amasty\QuoteAttributes\Model\QuoteEntity;

use Amasty\QuoteAttributes\Api\Data\QuoteEntityInterface;
use InvalidArgumentException;

class Registry
{
    public const REGISTRY_KEY_SEPARATOR = '-';

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var array
     */
    private $availableKeyFields;

    public function __construct(array $availableKeyFields = [])
    {
        $this->availableKeyFields = array_merge($availableKeyFields, [
            QuoteEntityInterface::QUOTE_ID,
            QuoteEntityInterface::ENTITY_ID
        ]);
    }

    /**
     * @param QuoteEntityInterface $quoteEntity
     * @return void
     */
    public function save(QuoteEntityInterface $quoteEntity): void
    {
        foreach ($this->availableKeyFields as $availableKeyField) {
            $key = $this->generateKey($availableKeyField, (string) $quoteEntity->getData($availableKeyField));
            $this->set($key, $quoteEntity);
        }
    }

    /**
     * @param string $key
     * @param QuoteEntityInterface $quoteEntity
     * @return void
     */
    public function set(string $key, QuoteEntityInterface $quoteEntity): void
    {
        $this->cache[$key] = $quoteEntity;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * @param string $key
     * @return QuoteEntityInterface
     */
    public function get(string $key): QuoteEntityInterface
    {
        return $this->cache[$key];
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @return string
     */
    public function generateKey(string $fieldName, string $value): string
    {
        if (!in_array($fieldName, $this->availableKeyFields)) {
            throw new InvalidArgumentException('Field name can\'t be used for key generation.');
        }
        return $fieldName . self::REGISTRY_KEY_SEPARATOR . $value;
    }
}
