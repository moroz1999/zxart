<?php

// no strict types: sources hand numeric import ids over as ints, and the
// import has always let PHP coerce them to the strings the lookups take

namespace ZxArt\Groups\Services;

use CountriesManager;
use groupElement;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;

/**
 * Brings what an import label says about a group into it: mostly filling what
 * is empty; the location follows the label, parent groups are added. Saves the
 * group when anything changed.
 *
 * @psalm-api
 */
final readonly class ImportedGroupMerger
{
    public function __construct(
        private CountriesManager $countriesManager,
        private ImportIdOperator $importIdOperator,
        private GroupRepository $groupRepository,
    ) {
    }

    public function merge(groupElement $element, GroupLabel $dto, ImportOrigin $origin): void
    {
        $changed = false;

        if (($dto->name !== '' && $dto->name !== null) && $element->title !== $dto->name && !$element->title) {
            $changed = true;
            $element->title = $dto->name;
            $element->structureName = $dto->name;
        }

        if (($dto->type !== '' && $dto->type !== null) && $element->type !== $dto->type) {
            if (!$element->type || $element->type === 'unknown') {
                $changed = true;
                $element->type = $dto->type;
            }
        }

        if (($dto->abbreviation !== '' && $dto->abbreviation !== null) && $element->abbreviation !== $dto->abbreviation && !$element->abbreviation) {
            $changed = true;
            $element->abbreviation = $dto->abbreviation;
        }

        if (($dto->website !== '' && $dto->website !== null) && $element->website !== $dto->website && !$element->website) {
            $changed = true;
            $element->website = $dto->website;
        }

        if ($dto->locationName !== '' && $dto->locationName !== null) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->locationName)) {
                if ($locationElement->structureType === 'country') {
                    if ($element->country != $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                } elseif ($locationElement->structureType === 'city') {
                    if ($element->city != $locationElement->id) {
                        $changed = true;
                        $element->city = $locationElement->id;
                        if ($countryId = $locationElement->getCountryId()) {
                            $element->country = $countryId;
                        }
                    }
                }
            }
        }

        if ($dto->countryName !== '' && $dto->countryName !== null) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->countryName)) {
                if ($locationElement->structureType === 'country') {
                    if ($element->country !== $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                }
            }
        }

        if ($dto->cityName !== '' && $dto->cityName !== null) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->cityName)) {
                if ($locationElement->structureType === 'city') {
                    if ($element->city !== $locationElement->id) {
                        $changed = true;
                        $element->city = $locationElement->id;
                    }
                }
            }
        }

        if ($dto->countryId !== 0 && $dto->countryId !== null) {
            $locationElement = $this->importIdOperator->getElementByImportId((string)$dto->countryId, $origin, EntityType::Country);
            if ($locationElement && $element->country !== $locationElement->id) {
                $changed = true;
                $element->country = $locationElement->id;
            }
        }

        if (is_array($dto->parentGroupIds)) {
            $parentGroups = $element->parentGroups;
            foreach ($dto->parentGroupIds as $groupId) {
                $groupElement = $this->importIdOperator->getElementByImportId((string)$groupId, $origin, EntityType::Group);
                if ($groupElement !== null && !in_array($groupElement, $parentGroups, true)) {
                    $parentGroups[] = $groupElement;
                }
            }
            $changed = true;
            $element->parentGroups = $parentGroups;
        }


        if ($changed) {
            $element->checkCountry();
            $this->groupRepository->save($element);
        }
    }
}
