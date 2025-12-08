<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods\Dto;

use ZxArt\Import\Labels\Label;
use ZxArt\Prods\LegalStatus;

final readonly class ProdImportDTO
{
    public function __construct(
        public string       $id,
        public ?string      $title = null,
        public ?string      $altTitle = null,
        public ?string      $description = null,
        /**
         * @var bool|null - text description is html or plain
         */
        public ?bool        $htmlDescription = null,
        public ?string      $instructions = null,
        /** @var string[]|null */
        public ?array       $languages = null,
        public ?LegalStatus $legalStatus = null,
        public ?string      $youtubeId = null,
        public ?string      $externalLink = null,
        public ?string      $compo = null,
        public ?int         $year = null,
        public ?array       $ids = null,
        public ?array       $importIds = null,
        /** @var Label[]|null */
        public ?array       $labels = null,
        /** @var array<string,string[]>|null map importAuthorId => roles[] */
        public ?array       $authorRoles = null,
        /** @var string[]|null */
        public ?array       $groups = null,
        /** @var string[]|null */
        public ?array       $publishers = null,
        /** @var array<string,string[]>|null */
        public ?array       $undetermined = null,
        public ?PartyRefDTO $party = null,
        /** @var int[]|null category ids (локальные) */
        public ?array       $directCategories = null,
        /** @var string[]|null importCategoryId[] (по importId) */
        public ?array       $categories = null,
        /** медиа */
        /** @var string[]|null */
        public ?array       $images = null,
        /** @var FileWithAuthorDTO[]|null */
        public ?array       $maps = null,
        /** @var string[]|null */
        public ?array       $inlayImages = null,
        /** @var FileWithAuthorDTO[]|null */
        public ?array       $rzx = null,
        /** связи */
        /** @var string[]|null import ids of prod or release */
        public ?array       $compilationItems = null,
        /** @var string[]|null import ids of prod */
        public ?array       $seriesProdIds = null,
        /** @var ArticleDTO[]|null */
        public ?array       $articles = null,
        /** @var ReleaseImportDTO[]|null */
        public ?array       $releases = null,
        public ?string      $origin = null,
    )
    {
    }

    /**
     * @deprecated
     */
    public static function fromArray(array $a): self
    {
        $labels = isset($a['labels'])
            ? array_map(static fn($x) => Label::fromArray($x), (array)$a['labels'])
            : null;

        $maps = isset($a['maps'])
            ? array_map(static fn($x) => FileWithAuthorDTO::fromArray($x), (array)$a['maps'])
            : null;

        $rzx = isset($a['rzx'])
            ? array_map(static fn($x) => FileWithAuthorDTO::fromArray($x), (array)$a['rzx'])
            : null;

        $articles = isset($a['articles'])
            ? array_map(static fn($x) => ArticleDTO::fromArray($x), (array)$a['articles'])
            : null;

        $releases = isset($a['releases'])
            ? array_map(static fn($x) => ReleaseImportDTO::fromArray($x), (array)$a['releases'])
            : null;

        return new self(
            id: (string)$a['id'],
            title: (string)($a['title'] ?? ''),
            altTitle: $a['altTitle'] ?? null,
            description: $a['description'] ?? null,
            languages: isset($a['language']) ? (array)$a['language'] : null,
            legalStatus: $a['legalStatus'] ?? null,
            youtubeId: $a['youtubeId'] ?? null,
            externalLink: $a['externalLink'] ?? null,
            compo: $a['compo'] ?? null,
            year: isset($a['year']) ? (int)$a['year'] : null,
            ids: isset($a['ids']) ? (array)$a['ids'] : null,
            importIds: isset($a['importIds']) ? (array)$a['importIds'] : null,
            labels: $labels,
            authorRoles: isset($a['authors']) ? (array)$a['authors'] : null,
            groups: isset($a['groups']) ? array_values((array)$a['groups']) : null,
            publishers: isset($a['publishers']) ? array_values((array)$a['publishers']) : null,
            undetermined: isset($a['undetermined']) ? (array)$a['undetermined'] : null,
            party: isset($a['party']) ? PartyRefDTO::fromArray($a['party']) : null,
            directCategories: isset($a['directCategories']) ? array_map('intval', (array)$a['directCategories']) : null,
            categories: isset($a['categories']) ? array_values((array)$a['categories']) : null,
            images: isset($a['images']) ? array_values((array)$a['images']) : null,
            maps: $maps,
            inlayImages: isset($a['inlayImages']) ? array_values((array)$a['inlayImages']) : null,
            rzx: $rzx,
            compilationItems: isset($a['compilationItems']) ? array_values((array)$a['compilationItems']) : null,
            seriesProdIds: isset($a['seriesProds']) ? array_values((array)$a['seriesProds']) : null,
            articles: $articles,
            releases: $releases,
            origin: isset($a['origin']) ? (array)$a['origin'] : null,
        );
    }

    public function withAddedRelease(ReleaseImportDTO $release): self
    {
        $updatedReleases = $this->releases !== null
            ? [...$this->releases, $release]
            : [$release];

        return new self(
            id: $this->id,
            title: $this->title,
            altTitle: $this->altTitle,
            description: $this->description,
            htmlDescription: $this->htmlDescription,
            instructions: $this->instructions,
            languages: $this->languages,
            legalStatus: $this->legalStatus,
            youtubeId: $this->youtubeId,
            externalLink: $this->externalLink,
            compo: $this->compo,
            year: $this->year,
            ids: $this->ids,
            importIds: $this->importIds,
            labels: $this->labels,
            authorRoles: $this->authorRoles,
            groups: $this->groups,
            publishers: $this->publishers,
            undetermined: $this->undetermined,
            party: $this->party,
            directCategories: $this->directCategories,
            categories: $this->categories,
            images: $this->images,
            maps: $this->maps,
            inlayImages: $this->inlayImages,
            rzx: $this->rzx,
            compilationItems: $this->compilationItems,
            seriesProdIds: $this->seriesProdIds,
            articles: $this->articles,
            releases: $updatedReleases,
            origin: $this->origin,
        );
    }

}
