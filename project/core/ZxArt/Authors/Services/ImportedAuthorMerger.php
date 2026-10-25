<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use CountriesManager;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Import\Authors\AuthorImportPolicy;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;

/**
 * Brings what an import label says about an author into it: a field is filled
 * when empty, overwritten only as the policy allows. Saves the author when
 * anything changed, then adds the group memberships the label lists.
 *
 * @psalm-api
 */
final readonly class ImportedAuthorMerger
{
    public function __construct(
        private CountriesManager $countriesManager,
        private ImportIdOperator $importIdOperator,
        private AuthorshipRepository $authorshipRepository,
        private AuthorRepository $authorRepository,
    ) {
    }

    public function merge(authorElement $element, PersonLabel $label, ImportOrigin $origin, AuthorImportPolicy $policy): void
    {
        $changed = false;

        $title = $label->name ?? null;
        if ($title !== null && $title !== '' && $element->title !== $title && !$element->title) {
            $element->title = $title;
            $element->structureName = $title;
            $changed = true;
        }

        if (($policy->forceUpdateRealName || $element->getRealName() === '') && !empty($label->realName) && $element->getRealName() !== $label->realName) {
            $element->realName = $label->realName;
            $changed = true;
        }

        if (!empty($label->locationName)) {
            $locationElement = $this->countriesManager->getLocationByName($label->locationName);
            if ($locationElement) {
                if ($locationElement->structureType === 'country') {
                    if (!$element->country || $policy->forceUpdateCountry) {
                        if ($element->country !== $locationElement->getId()) {
                            $element->country = $locationElement->getId();
                            $changed = true;
                        }
                    }
                } elseif ($locationElement->structureType === 'city') {
                    if (!$element->city || $policy->forceUpdateCity) {
                        if ($element->city !== $locationElement->getId()) {
                            $element->city = $locationElement->getId();
                            if ($countryId = $locationElement->getCountryId()) {
                                $element->country = $countryId;
                            }
                            $changed = true;
                        }
                    }
                }
            }
        }

        if (!empty($label->countryName)) {
            $locationElement = $this->countriesManager->getLocationByName($label->countryName);
            if ($locationElement && $locationElement->structureType === 'country') {
                if (($policy->forceUpdateCountry || !$element->country) && $element->country !== $locationElement->getId()) {
                    $element->country = $locationElement->getId();
                    $changed = true;
                }
            }
        }
        if (!empty($label->cityName)) {
            $locationElement = $this->countriesManager->getLocationByName($label->cityName);
            if ($locationElement) {
                if ($locationElement->structureType === 'city') {
                    if (($policy->forceUpdateCity || !$element->city) && $element->city !== $locationElement->getId()) {
                        $element->city = $locationElement->getId();
                        $changed = true;
                    }
                }
            }
        }

        if ($policy->forceUpdateCountry && $label->countryId !== null) {
            $countryElement = $this->importIdOperator->getElementByImportId((string)$label->countryId, $origin, EntityType::Country);
            if ($countryElement && $element->country !== $countryElement->getId()) {
                $element->country = $countryElement->getId();
                $changed = true;
            }
        }
        if ($policy->forceUpdateCity && $label->cityId !== null) {
            $cityElement = $this->importIdOperator->getElementByImportId((string)$label->cityId, $origin, EntityType::City);
            if ($cityElement && $element->city !== $cityElement->getId()) {
                $element->city = $cityElement->getId();
                $changed = true;
            }
        }

        if ($changed) {
            $element->checkCountry();
            $this->authorRepository->save($element);
        }

        if ($policy->forceUpdateGroups && is_array($label->groupIds) && $label->groupIds !== []) {
            $roles = $label->groupRoles ?? [];
            foreach ($label->groupIds as $groupImportId) {
                if ($groupImportId === null || $groupImportId === '') {
                    continue;
                }
                if ($groupElement = $this->importIdOperator->getElementByImportId($groupImportId, $origin, EntityType::Group)) {
                    $this->authorshipRepository->addAuthorship($groupElement->getId(), $element->getPersistedId(), EntityType::Group, $roles);
                }
            }
        }
    }
}
