<?php

declare(strict_types=1);

namespace ZxArt\EntityConversion\Services;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\EntityConversion\ConversionTarget;
use ZxArt\EntityConversion\Dto\ConvertedEntityDto;
use ZxArt\EntityConversion\Exception\EntityConversionException;
use ZxArt\Groups\Services\GroupsService;

/**
 * Turns an author, a group or one of their aliases into another kind of entity:
 * alias → author, author → group, group → author, group alias → group. The
 * source's works, links and import origins move to a newly created entity and
 * the source is deleted.
 */
final readonly class EntityConversionService
{
    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private AuthorsService $authorsService,
        private GroupsService $groupsService,
    ) {
    }

    public function convert(int $elementId, ConversionTarget $target): ConvertedEntityDto
    {
        $element = $this->getElement($elementId);

        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            $target->getPrivilege(),
            (string)$element->structureType,
        ) === true;
        if (!$isAllowed) {
            throw new EntityConversionException('Converting this element is forbidden', 403);
        }

        $converted = $this->runConversion($element, $target);
        if ($converted === null) {
            throw new EntityConversionException('The ' . $target->value . ' could not be created', 500);
        }

        return new ConvertedEntityDto(id: $converted->getId());
    }

    private function runConversion(structureElement $element, ConversionTarget $target): authorElement|groupElement|null
    {
        return match (true) {
            $element instanceof authorAliasElement && $target === ConversionTarget::Author
                => $this->authorsService->convertAliasToAuthor($element),
            $element instanceof authorElement && $target === ConversionTarget::Group
                => $this->groupsService->convertAuthorToGroup($element),
            $element instanceof groupElement && $target === ConversionTarget::Author
                => $this->authorsService->convertGroupToAuthor($element),
            $element instanceof groupAliasElement && $target === ConversionTarget::Group
                => $this->groupsService->convertGroupAliasToGroup($element),
            default => throw new EntityConversionException(
                'A ' . (string)$element->structureType . ' cannot be converted into a ' . $target->value,
                400,
            ),
        };
    }

    private function getElement(int $elementId): structureElement
    {
        if ($elementId <= 0) {
            throw new EntityConversionException('Missing required element id', 400);
        }
        $element = $this->structureManager->getElementById($elementId);
        if ($element === null) {
            throw new EntityConversionException('Element not found', 404);
        }

        return $element;
    }
}
