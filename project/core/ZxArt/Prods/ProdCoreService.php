<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use authorElement;
use groupElement;
use privilegesManager;
use structureElement;
use structureManager;
use tagElement;
use userElement;
use ZxArt\Prods\Dto\ProdAuthorInfoDto;
use ZxArt\Prods\Dto\ProdCategoryPathDto;
use ZxArt\Prods\Dto\ProdCategoryRefDto;
use ZxArt\Prods\Dto\ProdCoreDto;
use ZxArt\Prods\Dto\ProdGroupRefDto;
use ZxArt\Prods\Dto\ProdPrivilegesDto;
use ZxArt\Prods\Dto\ProdSubmitterDto;
use ZxArt\Prods\Dto\ProdTagRefDto;
use ZxArt\Prods\Dto\ProdVotingDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use zxProdCategoryElement;
use zxProdElement;

readonly class ProdCoreService
{
    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ProdInfoBuilder $infoBuilder,
    ) {
    }

    public function getCore(int $elementId): ProdCoreDto
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof zxProdElement) {
            throw new ProdDetailsException('Prod not found', 404);
        }

        $theme = $this->infoBuilder->resolveCurrentTheme();
        $legalStatus = $element->getLegalStatus();
        $year = $element->year;

        return new ProdCoreDto(
            elementId: $element->getId(),
            title: $this->infoBuilder->decodeText($element->title),
            altTitle: $this->infoBuilder->decodeText($element->altTitle),
            prodUrl: (string)$element->getUrl(),
            h1: (string)$element->getH1(),
            metaTitle: (string)$element->getMetaTitle(),
            year: $year,
            legalStatus: $legalStatus,
            legalStatusLabel: $this->infoBuilder->translate('legalstatus.' . $legalStatus),
            externalLink: $element->externalLink,
            youtubeId: $element->youtubeId,
            description: $element->getDescription(),
            htmlDescription: $element->htmlDescription,
            instructions: $element->instructions,
            generatedDescription: (string)$element->getGeneratedDescription(),
            dateCreated: $element->dateCreated,
            catalogueYearUrl: $year > 0 ? $element->getCatalogueUrl(['years' => $year]) : '',
            categoriesPaths: $this->buildCategoriesPaths($element),
            languages: $this->infoBuilder->buildLanguages($element),
            hardware: $this->infoBuilder->buildHardware($element),
            links: $this->infoBuilder->buildLinks($element, $theme),
            party: $this->infoBuilder->buildParty($element),
            authors: $this->buildAuthors($element),
            publishers: $this->buildGroupsRefs($element->publishers),
            groups: $this->buildGroupsRefs($element->groups),
            tags: $this->buildTags($element),
            voting: $this->buildVoting($element),
            submitter: $this->buildSubmitter($element),
            privileges: $this->buildPrivileges($elementId),
        );
    }

    /**
     * @return ProdCategoryPathDto[]
     */
    private function buildCategoriesPaths(zxProdElement $element): array
    {
        $paths = [];
        foreach ($element->getCategoriesPaths() as $rawPath) {
            $categories = [];
            foreach ($rawPath as $category) {
                if (!$category instanceof zxProdCategoryElement) {
                    continue;
                }
                $categories[] = new ProdCategoryRefDto(
                    id: $category->getId(),
                    title: $this->infoBuilder->decodeText($category->title),
                    url: (string)$category->getUrl(),
                );
            }
            if ($categories) {
                $paths[] = new ProdCategoryPathDto(categories: $categories);
            }
        }
        return $paths;
    }

    /**
     * @return ProdAuthorInfoDto[]
     */
    private function buildAuthors(zxProdElement $element): array
    {
        $authors = [];
        /**
         * @var list<array{authorElement: structureElement, roles?: list<string>}> $records
         */
        $records = $element->getAuthorsInfo(EntityType::Prod->value);
        foreach ($records as $info) {
            $authorElement = $info['authorElement'];
            if (!$authorElement instanceof authorElement) {
                continue;
            }
            $authors[] = new ProdAuthorInfoDto(
                id: $authorElement->getId(),
                title: $this->infoBuilder->decodeText($authorElement->title),
                url: (string)$authorElement->getUrl(),
                roles: $info['roles'] ?? [],
            );
        }
        return $authors;
    }

    /**
     * @param iterable<groupElement> $groups
     * @return ProdGroupRefDto[]
     */
    private function buildGroupsRefs(iterable $groups): array
    {
        $refs = [];
        foreach ($groups as $group) {
            if (!$group instanceof groupElement) {
                continue;
            }
            $refs[] = new ProdGroupRefDto(
                id: $group->getId(),
                title: $this->infoBuilder->decodeText($group->title),
                url: (string)$group->getUrl(),
            );
        }
        return $refs;
    }

    /**
     * @return ProdTagRefDto[]
     */
    private function buildTags(zxProdElement $element): array
    {
        $tags = [];
        foreach ($element->getTagsList() as $tag) {
            if (!$tag instanceof tagElement) {
                continue;
            }
            $tags[] = new ProdTagRefDto(
                id: $tag->getId(),
                title: $this->infoBuilder->decodeText($tag->title),
                url: (string)$tag->getUrl(),
            );
        }
        return $tags;
    }

    private function buildVoting(zxProdElement $element): ProdVotingDto
    {
        $denyVoting = $element->isVotingDenied();
        /**
         * @var int|string|null $userVoteRaw
         */
        $userVoteRaw = $element->getUserVote();
        $userVote = $userVoteRaw === null || $userVoteRaw === '' ? null : (int)$userVoteRaw;

        return new ProdVotingDto(
            votes: $element->votes,
            votesAmount: $element->votesAmount,
            userVote: $userVote,
            denyVoting: $denyVoting,
            votePercent: $denyVoting ? null : (float)$element->getVotePercent(),
        );
    }

    private function buildSubmitter(zxProdElement $element): ?ProdSubmitterDto
    {
        $user = $element->getUserElement();
        if (!$user instanceof userElement) {
            return null;
        }

        return new ProdSubmitterDto(
            id: $user->getId(),
            userName: $this->infoBuilder->decodeText($user->userName),
            url: (string)$user->getUrl(),
        );
    }

    private function buildPrivileges(int $elementId): ProdPrivilegesDto
    {
        return new ProdPrivilegesDto(
            showPublicForm: $this->hasPrivilege($elementId, 'showPublicForm', StructureType::ZxProd),
            showAiForm: $this->hasPrivilege($elementId, 'showAiForm', StructureType::ZxProd),
            resize: $this->hasPrivilege($elementId, 'resize', StructureType::ZxProd),
            join: $this->hasPrivilege($elementId, 'showJoinForm', StructureType::ZxProd),
            split: $this->hasPrivilege($elementId, 'showSplitForm', StructureType::ZxProd),
            publicDelete: $this->hasPrivilege($elementId, 'publicDelete', StructureType::ZxProd),
            addRelease: $this->hasPrivilege($elementId, 'publicAdd', StructureType::ZxRelease),
            addPressArticle: $this->hasPrivilege($elementId, 'publicReceive', StructureType::PressArticle),
        );
    }

    private function hasPrivilege(int $elementId, string $action, StructureType $structureType): bool
    {
        return $this->privilegesManager->checkPrivilegesForAction($elementId, $action, $structureType->value) === true;
    }
}
