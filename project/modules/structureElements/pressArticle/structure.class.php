<?php

/**
 * @property string $title
 * @property string $externalLink
 * @property string $introduction
 * @property string $content
 * @property boolean $allowComments
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

    public function setSearchTerm(string $term): void
    {
        $this->searchTerm = $term;
    }

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['introduction'] = 'html';
        $moduleStructure['content'] = 'html';
        $moduleStructure['shortContent'] = 'html';
        $moduleStructure['allowComments'] = 'checkbox';
        $moduleStructure['externalLink'] = 'url';
        $moduleStructure['authors'] = [
            'ConnectedElements',
            [
                'linkType' => 'pressAuthor',
                'role' => 'child',
            ],
        ];
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
        $multiLanguageFields[] = 'title';
        $multiLanguageFields[] = 'introduction';
        $multiLanguageFields[] = 'content';
        $multiLanguageFields[] = 'shortContent';

        $multiLanguageFields[] = 'h1';
        $multiLanguageFields[] = 'metaTitle';
        $multiLanguageFields[] = 'metaDescription';
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
        return null;
    }

    public function getSearchTitle(): string
    {
        $prod = $this->getFirstParentElement();
        return $prod->title . ' / ' . $this->title;
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

    public function getH1(): string
    {
        $parentElement = $this->getParent();
        return $parentElement->getTitle() . ': ' . $this->title . ' - ' . $this->introduction;
    }

    public function getFormattedContent(): string
    {
        $originalContent = $this->content;
        $content = strip_tags($originalContent, ['img', 'br']);
        return str_replace(["-\n", "\r"], '', $content);
    }

    public function getWrappedContent(): string
    {
        return str_replace("\n", '<br />', $this->getFormattedContent());
    }
}
