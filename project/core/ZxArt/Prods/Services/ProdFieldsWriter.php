<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use partyElement;
use structureElement;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\References\ReferenceTypes;
use zxProdCategoryElement;
use zxProdElement;
use zxReleaseElement;

/**
 * Writes the editable fields of a production and everything that follows from
 * them: the structure name, the party link, the tags, a missing year, the
 * categories and the AI passes that depend on them. Only the fields the DTO
 * carries are touched. Every reference is checked before anything is written.
 *
 * Checks no privilege and writes no log — the caller does, because creating,
 * editing through a form and uploading a batch each answer to their own action.
 *
 * @psalm-api
 */
final readonly class ProdFieldsWriter
{
    private const array COMPILATION_ITEM_TYPES = [zxProdElement::class, zxReleaseElement::class];
    private const array SERIES_PROD_TYPES = [zxProdElement::class];
    private const array CATEGORY_TYPES = [zxProdCategoryElement::class];

    public function __construct(
        private EntityReferenceResolver $entityReferenceResolver,
        private QueueService $queueService,
        private ProdRepository $prodRepository,
    ) {
    }

    public function apply(zxProdElement $prod, ProdFieldsDto $fields): void
    {
        $references = $this->loadReferences($fields);
        $prodId = $prod->getPersistedId();
        $oldCategoryIds = $fields->categoryIds !== null ? array_map('intval', (array)$prod->getConnectedCategoriesIds()) : [];

        $this->writeValues($prod, $fields, $references);
        if ($fields->partyId !== null) {
            $prod->renewPartyLink();
        }
        if ($fields->tags !== null) {
            $prod->updateTagsFromList($fields->tags);
        }
        $prod->updateYear();
        $this->prodRepository->save($prod);
        if ($fields->categoryIds !== null) {
            $prod->checkAndPersistCategories();
        }

        $this->updateAiQueue($prodId, $fields, $oldCategoryIds);
    }

    /**
     * @return array<string, structureElement[]> element field => loaded elements
     */
    private function loadReferences(ProdFieldsDto $fields): array
    {
        try {
            $this->entityReferenceResolver->assertHardwareCodes($fields->hardware ?? []);
            $this->entityReferenceResolver->assertLanguageCodes($fields->languages ?? []);
            if ($fields->partyId !== null && $fields->partyId > 0) {
                $this->entityReferenceResolver->element($fields->partyId, [partyElement::class], 'party');
            }
            $this->entityReferenceResolver->elements($fields->categoryIds ?? [], self::CATEGORY_TYPES, 'category');

            $references = [];
            $lists = [
                'groups' => [$fields->groupIds, ReferenceTypes::GROUPS],
                'publishers' => [$fields->publisherIds, ReferenceTypes::PUBLISHERS],
                'compilationItems' => [$fields->compilationItemIds, self::COMPILATION_ITEM_TYPES],
                'seriesProds' => [$fields->seriesProdIds, self::SERIES_PROD_TYPES],
            ];
            foreach ($lists as $field => [$ids, $types]) {
                if ($ids !== null) {
                    $references[$field] = $this->entityReferenceResolver->elements($ids, $types, $field);
                }
            }
        } catch (InvalidReferenceException $exception) {
            throw new ProdDataException($exception->getMessage(), 400, $exception);
        }

        return $references;
    }

    /**
     * @param array<string, structureElement[]> $references
     */
    private function writeValues(zxProdElement $prod, ProdFieldsDto $fields, array $references): void
    {
        $values = [
            'title' => $fields->title,
            'structureName' => $fields->title,
            'altTitle' => $fields->altTitle,
            'year' => $fields->year,
            'description' => $fields->description,
            'htmlDescription' => $fields->htmlDescription,
            'instructions' => $fields->instructions,
            'htmlInstructions' => $fields->htmlInstructions,
            'legalStatus' => $fields->legalStatus?->name,
            'externalLink' => $fields->externalLink,
            'youtubeId' => $fields->youtubeId,
            'party' => $fields->partyId,
            'partyplace' => $fields->partyPlace,
            'compo' => $fields->compo,
            'denyVoting' => $fields->denyVoting,
            'denyComments' => $fields->denyComments,
            'language' => $fields->languages,
            'hardwareRequired' => $fields->hardware,
            'categories' => $fields->categoryIds,
            ...$references,
        ];
        ElementFields::assign($prod, $values);
    }

    /**
     * Categories and tags chosen by a person need no AI pass over them, and the
     * AI texts describe the categories, so a change of those writes them anew.
     *
     * @param int[] $oldCategoryIds
     */
    private function updateAiQueue(int $prodId, ProdFieldsDto $fields, array $oldCategoryIds): void
    {
        if ($fields->categoryIds === null && $fields->tags === null) {
            return;
        }
        $this->queueService->updateStatus($prodId, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);

        $isCategoriesChanged = $fields->categoryIds !== null
            && (array_diff($oldCategoryIds, $fields->categoryIds) !== [] || array_diff($fields->categoryIds, $oldCategoryIds) !== []);
        if ($isCategoriesChanged) {
            $this->queueService->updateStatus($prodId, QueueType::AI_SEO, QueueStatus::STATUS_TODO);
            $this->queueService->updateStatus($prodId, QueueType::AI_INTRO, QueueStatus::STATUS_TODO);
        }
    }
}
