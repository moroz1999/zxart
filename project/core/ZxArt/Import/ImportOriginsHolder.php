<?php

declare(strict_types=1);

namespace ZxArt\Import;

use ZxArt\Shared\EntityType;

/**
 * An entity whose portal ids are editable in its form.
 */
interface ImportOriginsHolder
{
    /**
     * Type the portal ids of this entity are recorded under. Aliases share the
     * type of the entity they stand for.
     */
    public function getImportEntityType(): EntityType;

    /**
     * @return list<array{origin: string, importId: string}>
     */
    public function getImportOrigins(): array;

    /**
     * @return list<array{value: string, label: string}>
     */
    public function getImportOriginOptions(): array;

    public function persistImportOrigins(): void;
}
