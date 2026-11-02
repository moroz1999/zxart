<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use authorAliasElement;
use authorElement;
use cityElement;
use countryElement;
use groupAliasElement;
use groupElement;
use partyElement;
use structureManager;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Urls\EntityUrlResolver;
use zxProdCategoryElement;
use zxProdElement;
use zxReleaseElement;

/**
 * Titles and SPA URLs of the entities a message mentions. Productions,
 * releases, authors, groups and their aliases, and the categories, parties,
 * countries and cities their fields name are looked up; a mention of anything
 * else, of an element that does not exist or is of another type, is left out.
 *
 * @psalm-api
 */
final readonly class EntityMentionLookup
{
    public function __construct(
        private structureManager $structureManager,
        private EntityUrlResolver $entityUrlResolver,
    ) {
    }

    /**
     * @param EntityMentionDto[] $refs
     * @return array<string, MentionedEntityDto> by {@see EntityMentionDto::key()}
     */
    public function lookup(array $refs): array
    {
        $found = [];
        foreach ($refs as $ref) {
            $key = $ref->key();
            if (isset($found[$key])) {
                continue;
            }
            $title = $this->lookupOne($ref);
            if ($title !== null) {
                $found[$key] = $title;
            }
        }

        return $found;
    }

    private function lookupOne(EntityMentionDto $ref): ?MentionedEntityDto
    {
        [$class, $structureType] = match ($ref->type) {
            EntityType::Prod => [zxProdElement::class, StructureType::ZxProd],
            EntityType::Release => [zxReleaseElement::class, StructureType::ZxRelease],
            EntityType::Author => [authorElement::class, StructureType::Author],
            EntityType::AuthorAlias => [authorAliasElement::class, StructureType::AuthorAlias],
            EntityType::Group => [groupElement::class, StructureType::Group],
            EntityType::GroupAlias => [groupAliasElement::class, StructureType::GroupAlias],
            EntityType::Category => [zxProdCategoryElement::class, StructureType::ZxProdCategory],
            EntityType::Party => [partyElement::class, StructureType::Party],
            EntityType::Country => [countryElement::class, StructureType::Country],
            EntityType::City => [cityElement::class, StructureType::City],
            default => [null, null],
        };
        if ($class === null || $structureType === null || $ref->id <= 0) {
            return null;
        }
        $element = $this->structureManager->getElementById($ref->id);
        if (!$element instanceof $class) {
            return null;
        }
        $url = $this->entityUrlResolver->resolveByType($structureType->value, $ref->id);
        if ($url === null) {
            return null;
        }

        return new MentionedEntityDto(ElementFields::text($element, 'title'), $url);
    }
}
