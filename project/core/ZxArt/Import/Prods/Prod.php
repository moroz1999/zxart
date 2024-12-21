<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods;

use ZxArt\Import\Labels\GroupLabel;

readonly final class Prod
{
    /**
     * @param GroupLabel[]|null $groupIds
     */
    public function __construct(
        public ?string $id = null,
        public ?string $title = null,
        public ?string $altTitle = null,
        public ?string $legalStatus = null,
        public ?int    $year = null,
        public ?string $compo = null,
        public ?string $description = null,
        public ?array  $party = null, // содержит ['title' => ..., 'year' => ..., 'place' => ...]
        public ?string $youtubeId = null,
        public ?string $externalLink = null,
        public ?string $language = null,
        public ?array  $labels = null,
        public ?array  $directCategories = null,
        public ?array  $undetermined = null, // содержит подмассивы 'group' и 'author'
        public ?array  $authorRoles = null, // массив <authorId, roles>
        public ?array  $groupIds = null,
        public ?array  $publisherIds = null,
        public ?array  $compilationItems = null,
        public ?array  $seriesProds = null,
        public ?array  $articles = null, // массив с полями 'title', 'introduction', 'externalLink', 'content'
        public ?array  $categories = null,
        public ?array  $images = null,
        public ?array  $maps = null, // содержит ['url' => ..., 'author' => ...]
        public ?array  $inlayImages = null,
        public ?array  $rzx = null,
        public ?array  $importIds = null,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ?: $this->title,
            'title' => $this->title,
            'altTitle' => $this->altTitle,
            'legalStatus' => $this->legalStatus,
            'year' => $this->year,
            'compo' => $this->compo,
            'description' => $this->description,
            'party' => $this->party,
            'youtubeId' => $this->youtubeId,
            'externalLink' => $this->externalLink,
            'language' => $this->language,
            'labels' => $this->labels,
            'directCategories' => $this->directCategories,
            'undetermined' => $this->undetermined,
            'authors' => $this->authorRoles,
            'groups' => $this->groupIds,
            'publishers' => $this->publisherIds,
            'compilationItems' => $this->compilationItems,
            'seriesProds' => $this->seriesProds,
            'articles' => $this->articles,
            'categories' => $this->categories,
            'images' => $this->images,
            'maps' => $this->maps,
            'inlayImages' => $this->inlayImages,
            'rzx' => $this->rzx,
            'importIds' => $this->importIds,
        ];
    }
}
