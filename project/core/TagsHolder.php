<?php
declare(strict_types=1);

use App\Logging\EventsLog;

trait TagsHolder
{
    protected $tagsList;

    public function updateTagsInfo(): void
    {
        $tagsIndex = $this->getTagsIndex();

        $updatedTagsStrings = 0;
        $amountBeforeUpdate = (int)$this->tagsAmount;

        $tagsStrings = explode(',', $this->tagsText);
        $tagsManager = $this->getService('tagsManager');
        foreach ($tagsStrings as $tagName) {
            if ($tagElement = $tagsManager->addTag($tagName, $this->getPersistedId())) {
                if (isset($tagsIndex[$tagElement->title])) {
                    unset($tagsIndex[$tagElement->title]);
                }
                $updatedTagsStrings++;
            }
        }

        $this->tagsAmount = $updatedTagsStrings;

        for ($i = 0; $i < $updatedTagsStrings - $amountBeforeUpdate; $i++) {
            $this->getService(EventsLog::class)->logEvent($this->id, 'tagAdded');
        }


        foreach ($tagsIndex as $tagElement) {
            $tagsManager->removeTag($tagElement->title, $this->id);
        }
    }

    public function addTags(array $tagsStrings): void
    {
        $tagsManager = $this->getService('tagsManager');
        foreach ($tagsStrings as $tagName) {
            $tagsManager->addTag($tagName, $this->getPersistedId());
        }
        $this->tagsList = null;
        $this->tagsAmount = count($this->getTagsList());
    }

    public function getTagsIndex(): array
    {
        $index = [];
        foreach ($this->getTagsList() as $tag) {
            $index[$tag->title] = $tag;
        }
        return $index;
    }

    public function getTagsList()
    {
        if ($this->tagsList === null) {
            $this->tagsList = [];
            $sectionLogics = $this->getService('SectionLogics');;
            $sectionId = $sectionLogics->getSectionIdByType($this->sectionType);
            $tagsManager = $this->getService('tagsManager');
            $structureManager = $this->getService('structureManager');
            if ($idList = $tagsManager->getTagsIdList($this->id)) {
                foreach ($idList as $id) {
                    if ($tagElement = $structureManager->getElementById($id, $sectionId)) {
                        $this->tagsList[] = $tagElement;
                    }
                }
            }

            $sort = [];
            foreach ($this->tagsList as $tag) {
                $sort[] = mb_strtolower($tag->title);
            }
            array_multisort($sort, SORT_ASC, $this->tagsList);
        }
        return $this->tagsList;
    }

    /**
     * @return false|int
     *
     * @psalm-return false|int<0, max>
     */
    public function hasTag($text): int|false
    {
        $result = false;
        if ($tagsTexts = $this->getTagsTexts()) {
            foreach ($tagsTexts as $tagsText) {
                if ($result = stripos($tagsText, $text)) {
                    break;
                }
            }
        }
        return $result;
    }

    public function getSuggestedTags()
    {
        $tagsManager = $this->getService('tagsManager');
        return $tagsIdList = $tagsManager->getElementSuggestedTags($this->id, 25);
    }

    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getTagsTexts(): array
    {
        $tagsTexts = [];
        foreach ($this->getTagsList() as $tag) {
            $tagsTexts[] = $tag->title;
        }
        return $tagsTexts;
    }

    public function generateTagsText(): string
    {
        return implode(', ', $this->getTagsTexts());
    }

}