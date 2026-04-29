<?php

declare(strict_types=1);

namespace ZxArt\ElementPrivileges;

use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ElementPrivileges\Dto\ElementPrivilegesDto;
use ZxArt\Tags\Exception\TagsException;

readonly class ElementPrivilegesService
{
    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
    ) {
    }

    /**
     * @param string[] $requestedPrivileges
     */
    public function getPrivileges(int $elementId, array $requestedPrivileges): ElementPrivilegesDto
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof structureElement) {
            throw new TagsException('Element not found', 404);
        }

        $normalizedPrivileges = [];
        foreach ($requestedPrivileges as $privilegeName) {
            $normalizedPrivilegeName = trim($privilegeName);
            if ($normalizedPrivilegeName === '') {
                continue;
            }

            $normalizedPrivileges[$normalizedPrivilegeName] = $this->privilegesManager->checkPrivilegesForAction(
                $elementId,
                $normalizedPrivilegeName,
                (string)$element->structureType,
            ) === true;
        }

        return new ElementPrivilegesDto(
            elementId: $elementId,
            privileges: $normalizedPrivileges,
        );
    }
}
