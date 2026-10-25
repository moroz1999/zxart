<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Elements;

/**
 * An element without the CMS data layer: assigned fields land in `$values` and
 * the persisting steps are recorded in `$calls` in the order they ran.
 */
trait ElementSpyTrait
{
    /** @var array<string, mixed> */
    public array $values = [];
    /** @var list<string> */
    public array $calls = [];

    public function __set($propertyName, $value)
    {
        $this->values[$propertyName] = $value;
    }

    public function __get($propertyName)
    {
        return $this->values[$propertyName] ?? null;
    }

    public function __isset($propertyName)
    {
        return isset($this->values[$propertyName]);
    }

    public function getDataChunk($propertyName, $languageId = null): ?\DataChunk
    {
        return null;
    }

    public function getId(): int
    {
        return $this->elementId;
    }

    public function getPersistedId(): int
    {
        return $this->elementId;
    }

    public function getCreatedTimestamp(): int
    {
        return $this->createdTimestamp;
    }

    public function persistElementData()
    {
        $this->calls[] = 'persistElementData';
    }
}
