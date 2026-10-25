<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use authorAliasElement;
use authorElement;
use cityElement;
use countryElement;
use groupAliasElement;
use groupElement;
use LogicException;
use structureElement;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\EntityType;
use zxProdCategoryElement;
use zxProdElement;
use zxReleaseElement;

/**
 * Names an element as an entity ref: its type, id and decoded title.
 */
final class EntityRefs
{
    /**
     * @throws LogicException for an element no entity type stands for
     */
    public static function of(structureElement $element): EntityRefDto
    {
        $type = match (true) {
            $element instanceof authorElement => EntityType::Author,
            $element instanceof authorAliasElement => EntityType::AuthorAlias,
            $element instanceof groupElement => EntityType::Group,
            $element instanceof groupAliasElement => EntityType::GroupAlias,
            $element instanceof zxProdElement => EntityType::Prod,
            $element instanceof zxReleaseElement => EntityType::Release,
            $element instanceof zxProdCategoryElement => EntityType::Category,
            $element instanceof countryElement => EntityType::Country,
            $element instanceof cityElement => EntityType::City,
            default => throw new LogicException('No entity type stands for ' . $element::class),
        };
        $title = html_entity_decode(ElementFields::text($element, 'title'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return new EntityRefDto($type, $element->getId(), $title);
    }

    /**
     * @param iterable<structureElement> $elements
     * @return list<EntityRefDto>
     */
    public static function list(iterable $elements): array
    {
        $refs = [];
        foreach ($elements as $element) {
            $refs[] = self::of($element);
        }

        return $refs;
    }
}
