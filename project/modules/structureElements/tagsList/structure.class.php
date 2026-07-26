<?php

use ZxArt\TagsList\Dto\TagListItemDto;
use ZxArt\TagsList\TagsListService;

class tagsListElement extends structureElement
{
    private const float MINIMUM_FONT_SIZE = 1.0;
    private const float MAXIMUM_FONT_SIZE = 4.0;

    public $dataResourceName = 'module_tagslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'container';
    public int $maxAmount = 0;

    /**
     * @var list<tagElement>|null
     */
    protected ?array $tagsList = null;

    /**
     * @var array<int, int>
     */
    protected array $tagAmounts = [];

    /**
     * @return void
     */
    #[\Override]
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    /**
     * @return list<tagElement>
     */
    public function getAllTags(): array
    {
        if ($this->tagsList === null) {
            $sectionsLogics = $this->getService(SectionLogics::class);
            $section = (string)$sectionsLogics->getArtItemsType();
            $tagsListService = $this->getService(TagsListService::class);
            $items = $tagsListService->getSectionTags($section, TagsListService::MINIMUM_ALLOWED_AMOUNT);

            $idList = array_map(static fn(TagListItemDto $item): int => $item->id, $items);
            $structureManager = $this->getService(structureManager::class);
            $loadedElements = $structureManager->getElementsByIdList($idList, $this->getId(), true);
            $this->tagsList = [];
            foreach ($loadedElements as $loadedElement) {
                if ($loadedElement instanceof tagElement) {
                    $this->tagsList[] = $loadedElement;
                }
            }
            $this->tagAmounts = [];
            foreach ($items as $item) {
                $this->tagAmounts[$item->id] = $item->amount;
            }

            $sort = [];
            $maxAmount = 0;
            foreach ($this->tagsList as $tag) {
                $sort[] = strtolower($tag->title);
                $amount = $this->getTagAmount($tag);
                if ($amount > $maxAmount) {
                    $maxAmount = $amount;
                }
            }
            $configuredMaxAmount = (int)$this->getService(ConfigManager::class)->get('zx.maxTagsAmount');
            if ($maxAmount > $configuredMaxAmount) {
                $maxAmount = $configuredMaxAmount;
            }
            $this->maxAmount = $maxAmount;
            array_multisort($sort, SORT_ASC, $this->tagsList);
        }
        return $this->tagsList;
    }

    public function getTagAmount(tagElement $tag): int
    {
        return $this->tagAmounts[(int)$tag->id] ?? 0;
    }

    public function getTagFontSize(int $amount): float
    {
        if ($this->maxAmount === 0) {
            return self::MINIMUM_FONT_SIZE;
        }

        $cappedAmount = min($amount, $this->maxAmount);

        $relativeAmount = ((float)$cappedAmount - 1.0) / (float)$this->maxAmount;

        return self::MINIMUM_FONT_SIZE
            + (self::MAXIMUM_FONT_SIZE - self::MINIMUM_FONT_SIZE) * $relativeAmount;
    }
}
