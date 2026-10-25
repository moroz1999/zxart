<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;

/**
 * Kinds of element a reference of several entity types may name.
 */
final class ReferenceTypes
{
    /** Who may publish a production or a release: groups, people and their aliases. */
    public const array PUBLISHERS = [
        groupElement::class,
        groupAliasElement::class,
        authorElement::class,
        authorAliasElement::class,
    ];

    public const array GROUPS = [groupElement::class, groupAliasElement::class];
}
