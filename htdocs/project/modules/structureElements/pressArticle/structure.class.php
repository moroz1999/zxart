<?php

/**
 * @property string $title
 * @property string $externalLink
 * @property string $introduction
 * @property string $content
 * @property boolean $allowComments
 * @property boolean $hasAiData
 * @property authorElement[]|authorAliasElement[] $authors
 */
class pressArticleElement extends structureElement implements SearchContentHolder, MetadataProviderInterface
{
    use CommentsTrait;
    use MetadataProviderTrait;

    public $dataResourceName = 'module_pressarticle';
    public $defaultActionName = 'show';
    public $role = 'content';
    private $searchTerm;
    private $metaData;


    public function setSearchTerm(string $term): void
    {
        $this->searchTerm = $term;
    }


    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['externalLink'] = 'url';
        $moduleStructure['introduction'] = 'html';
        $moduleStructure['content'] = 'html';
        $moduleStructure['allowComments'] = 'checkbox';
        $moduleStructure['authors'] = [
            'ConnectedElements',
            [
                'linkType' => 'pressAuthor',
                'role' => 'child',
            ],
        ];
        $moduleStructure['hasAiData'] = 'checkbox';
    }

    public function getCommentFormActionURL()
    {
        if ($parent = $this->getParent()) {
            return $parent->getFormActionURL();
        }
        return false;
    }

    public function getParent()
    {
        $structureManager = $this->getService('structureManager');
        if ($parent = $structureManager->getElementsFirstParent($this->id)) {
            return $parent;
        }
        return false;
    }

    public function getSearchTitle(): string
    {
        return $this->title;
    }

    public function getSearchContent(): string
    {
        $searchCutLength = 200;

        $textValue = html_entity_decode(strip_tags($this->content), ENT_QUOTES, 'UTF-8');

        if (($position = mb_stripos($textValue, $this->searchTerm)) === false) {
            return '';
        }
        $phraseLength = mb_strlen($this->searchTerm);

        $textLength = mb_strlen($textValue);

        if ($position > $searchCutLength) {
            $start = $position - $searchCutLength;
        } else {
            $start = 0;
        }

        if ($position + $phraseLength + $searchCutLength < $textLength) {
            $end = $position + $phraseLength + $searchCutLength;
        } else {
            $end = $textLength;
        }
        $searchContentLength = $end - $start;
        $searchContent = mb_substr($textValue, $start, $searchContentLength);

        if ($start > 0) {
            $searchContent = '...' . $searchContent;
        }

        if ($end < $textLength) {
            $searchContent = $searchContent . '...';
        }
        $searchContent = htmlspecialchars($searchContent, ENT_QUOTES, 'UTF-8');
        $searchContent = preg_replace('/' . $this->searchTerm . '/i', '<span class="search_emphasized">' . $this->searchTerm . '</span>', $searchContent);

        return $searchContent;
    }

    public function getH1()
    {
        $parentElement = $this->getParent();
        return $parentElement->getTitle() . ': ' . $this->title . ' - ' . $this->introduction;
    }

    public function getMetaTitle()
    {
        if ($this->hasAiData) {
            $metaData = $this->getMetaData();
            return $metaData['metaTitle'];
        }
        return $this->title . ' - ' . $this->introduction;
    }

    private function getMetaData()
    {
        if (!$this->metaData) {
            $db = $this->getService('db');
            $this->metaData = $db->table('module_pressarticle_meta')
                ->select(['metaTitle', 'metaDescription', 'generatedDescription'])
                ->where('id', '=', $this->id)
                ->first();
        }
        return $this->metaData;
    }

    public function getMetaDescription()
    {
        if ($this->hasAiData) {
            $metaData = $this->getMetaData();
            return $metaData['metaDescription'];
        }
        return mb_substr($this->content, 0, 180);
    }

    public function getGeneratedDescription()
    {
        if ($this->hasAiData) {
            $metaData = $this->getMetaData();
            return $metaData['generatedDescription'];
        }
        return '';
    }


}
