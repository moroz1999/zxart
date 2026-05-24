<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use groupAliasElement;
use groupElement;
use structureManager;
use ZxArt\Authors\Dto\AuthorCollaboratorGroupDto;
use ZxArt\Authors\Dto\AuthorCollaboratorPersonDto;
use ZxArt\Authors\Repositories\AuthorCollaboratorsRepository;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Shared\EntityType;

readonly final class AuthorCollaboratorsService
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorCollaboratorsRepository $collaboratorsRepository,
        private AuthorshipRepository $authorshipRepository,
    ) {
    }

    /**
     * @return array{people: AuthorCollaboratorPersonDto[], groups: AuthorCollaboratorGroupDto[]}
     */
    public function getCollaborators(int $authorId): array
    {
        $author = $this->structureManager->getElementById($authorId);
        if (!$author instanceof authorElement) {
            throw new ProdDetailsException('Author not found', 404);
        }

        $authorIds = $this->collaboratorsRepository->getAuthorAndAliasIds($authorId);

        return [
            'people' => $this->buildPeople($authorIds),
            'groups' => $this->buildGroups($authorId),
        ];
    }

    /**
     * @param int[] $authorIds
     * @return AuthorCollaboratorPersonDto[]
     */
    private function buildPeople(array $authorIds): array
    {
        $stats = $this->collaboratorsRepository->findCoAuthorStats($authorIds);
        $people = [];
        foreach ($stats as $row) {
            $element = $this->structureManager->getElementById($row['coAuthorId']);
            if (!$element instanceof authorElement) {
                continue;
            }
            $people[] = new AuthorCollaboratorPersonDto(
                id: (int)$element->id,
                title: html_entity_decode($element->getTitle(), ENT_QUOTES),
                url: (string)$element->getUrl(),
                jointPictures: $row['pictures'],
                jointTunes: $row['tunes'],
                jointProds: $row['prods'],
                jointTotal: $row['total'],
            );
        }
        return $people;
    }

    /** @return AuthorCollaboratorGroupDto[] */
    private function buildGroups(int $authorId): array
    {
        /** @var list<array{elementId: int, startDate: string, endDate: string}> $records */
        $records = $this->authorshipRepository->getAuthorshipRecords($authorId, EntityType::Group);
        $groups = [];
        foreach ($records as $record) {
            $element = $this->structureManager->getElementById($record['elementId']);
            if (!($element instanceof groupElement) && !($element instanceof groupAliasElement)) {
                continue;
            }
            $years = $this->formatYearsRange($record['startDate'] ?? '', $record['endDate'] ?? '');
            $members = $this->collaboratorsRepository->countGroupMembers((int)$element->id);
            $groups[] = new AuthorCollaboratorGroupDto(
                id: (int)$element->id,
                title: html_entity_decode($element->title, ENT_QUOTES),
                url: (string)$element->getUrl(),
                years: $years,
                membersCount: $members,
            );
        }
        return $groups;
    }

    private function formatYearsRange(string $startDate, string $endDate): ?string
    {
        if ($startDate === '' && $endDate === '') {
            return null;
        }
        $startYear = $startDate !== '' ? substr($startDate, 6, 4) : '';
        $endYear = $endDate !== '' ? substr($endDate, 6, 4) : '';
        if ($startYear === $endYear) {
            return $startYear;
        }
        if ($startYear !== '' && $endYear !== '') {
            return $startYear . '–' . $endYear;
        }
        return $startYear !== '' ? $startYear . '–' : '–' . $endYear;
    }
}
