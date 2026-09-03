<?php
declare(strict_types=1);

use App\Logging\EventsLog;

trait TagsHolder
{
    protected $tagsList;

    /**
     * Applies the tag titles the form submitted. Tags live only as links, so the
     * submitted list is the whole list: titles it no longer carries are unlinked.
     */
    public function updateTagsInfo(): void
    {
        /** @var array<array-key, mixed> $submittedTags */
        $submittedTags = (array)$this->getValue('tags');
        $titles = [];
        foreach ($submittedTags as $title) {
            $titles[] = (string)$title;
        }
        $this->updateTagsFromList($titles);
    }

    /**
     * @param string[] $tagNames
     */
    public function updateTagsFromList(array $tagNames): void
    {
        $tagsIndex = $this->getTagsIndex();

        $updatedTagsStrings = 0;
        $amountBeforeUpdate = (int)$this->tagsAmount;

        $tagsManager = $this->getService(tagsManager::class);
        foreach ($tagNames as $tagName) {
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

        $this->resetTagsCache();
    }

    public function addTags(array $tagsStrings): void
    {
        $tagsManager = $this->getService(tagsManager::class);
        foreach ($tagsStrings as $tagName) {
            $tagsManager->addTag($tagName, $this->getPersistedId());
        }
        $this->resetTagsCache();
        $this->tagsAmount = count($this->getTagsList());
    }

    public function resetTagsCache(): void
    {
        $this->tagsList = null;
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
            $sectionLogics = $this->getService(SectionLogics::class);
            $sectionId = $sectionLogics->getSectionIdByType($this->sectionType);
            $tagsManager = $this->getService(tagsManager::class);
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

    public function getSuggestedTags()
    {
        return $this->getService(tagsManager::class)->getElementSuggestedTags($this->id, 25);
    }

    /**
     * Titles of the tags linked to this item, as the form lists them.
     *
     * @return list<string>
     */
    public function getTagsTexts(): array
    {
        $tagsTexts = [];
        foreach ($this->getTagsList() as $tag) {
            $tagsTexts[] = (string)$tag->title;
        }
        return $tagsTexts;
    }

    /** Tag titles as one line, for the item's keywords in structured data. */
    public function generateTagsText(): string
    {
        return implode(', ', $this->getTagsTexts());
    }

}
