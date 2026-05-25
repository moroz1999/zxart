<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use structureManager;
use ZxArt\Authors\Dto\AuthorCollaboratorGroupDto;
use ZxArt\Authors\Dto\AuthorCollaboratorPersonDto;
use ZxArt\Authors\Repositories\AuthorCollaboratorsRepository;
use ZxArt\Prods\Exception\ProdDetailsException;

readonly final class AuthorCollaboratorsService
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorCollaboratorsRepository $collaboratorsRepository,
    ) {
    }

    /**
     * @return array{people: AuthorCollaboratorPersonDto[], groups: AuthorCollaboratorGroupDto[]}
     */
    public function getCollaborators(int $authorId): array
    {
        $author = $this->structureManager->getElementById($authorId);
        if (!($author instanceof authorElement) && !($author instanceof authorAliasElement)) {
            throw new ProdDetailsException('Author or alias not found', 404);
        }

        $authorIds = $this->collaboratorsRepository->getAuthorAndAliasIds($authorId);

        return [
            'people' => $this->buildPeople($authorIds),
            'groups' => $this->buildGroups($authorIds),
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

    /**
     * @param int[] $authorIds
     * @return AuthorCollaboratorGroupDto[]
     */
    private function buildGroups(array $authorIds): array
    {
        $records = $this->collaboratorsRepository->findGroupStats($authorIds);
        $groups = [];
        foreach ($records as $record) {
            $element = $this->structureManager->getElementById($record['groupId']);
            if (!($element instanceof groupElement) && !($element instanceof groupAliasElement)) {
                continue;
            }
            $years = $this->formatYears($record['years']);
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

    /**
     * @param int[] $years
     */
    private function formatYears(array $years): ?string
    {
        if (empty($years)) {
            return null;
        }

        sort($years);
        $first = (string)reset($years);
        $last = (string)end($years);
        if ($first === $last) {
            return $first;
        }

        return $first . '-' . $last;
    }
}
