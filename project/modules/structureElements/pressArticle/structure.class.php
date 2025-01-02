<?php

use ZxArt\Helpers\HtmlTagsStripper;
use ZxArt\LinkTypes;
use ZxArt\Queue\QueueStatusProvider;

/**
 * @property string $title
 * @property string $externalLink
 * @property string $introduction
 * @property string $content
 * @property string $h1
 * @property boolean $allowComments
 * @property boolean $aiRestartFix
 * @property boolean $aiRestartTranslate
 * @property boolean $aiRestartParse
 * @property boolean $aiRestartSeo
 * @property authorElement[]|authorAliasElement[] $authors
 * @property authorElement[]|authorAliasElement[] $people
 * @property zxProdElement[]|zxReleaseElement $software
 * @property groupElement[]|groupAliasElement[] $groups
 * @property partyElement[] $parties
 * @property zxMusicElement[] $tunes
 * @property zxPictureElement[] $pictures
 */
class pressArticleElement extends structureElement implements SearchContentHolder, MetadataProviderInterface
{
    use CommentsTrait;
    use MetadataProviderTrait;
    use TagsHolder;
    use QueueStatusProvider;

    public $dataResourceName = 'module_pressarticle';
    public $defaultActionName = 'show';
    public $role = 'content';
    private $searchTerm;

    protected ?int $tagsAmount = null;

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

        $moduleStructure['h1'] = 'text';
        $moduleStructure['metaTitle'] = 'text';
        $moduleStructure['metaDescription'] = 'text';

        $moduleStructure['aiRestartFix'] = 'checkbox';
        $moduleStructure['aiRestartTranslate'] = 'checkbox';
        $moduleStructure['aiRestartParse'] = 'checkbox';
        $moduleStructure['aiRestartSeo'] = 'checkbox';
        $moduleStructure['allowComments'] = 'checkbox';
        $moduleStructure['externalLink'] = 'url';
        $moduleStructure['authors'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_AUTHOR->value,
                'role' => 'child',
            ],
        ];
        $moduleStructure['people'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_PEOPLE->value,
                'role' => 'child',
            ],
        ];
        $moduleStructure['software'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_SOFTWARE->value,
                'role' => 'child',
            ],
        ];
        $moduleStructure['groups'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_GROUPS->value,
                'role' => 'child',
            ],
        ];
        $moduleStructure['parties'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_PARTIES->value,
                'role' => 'child',
            ],
        ];
        $moduleStructure['tunes'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_TUNES->value,
                'role' => 'child',
            ],
        ];
        $moduleStructure['pictures'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_PICTURES->value,
                'role' => 'child',
            ],
        ];

    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
        $multiLanguageFields[] = 'title';
        $multiLanguageFields[] = 'introduction';
        $multiLanguageFields[] = 'content';

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

    public function getParent(): ?zxProdElement
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
        return $prod?->getTitle() . ' / ' . $this->getTitle();
    }

    public function getSearchContent(): string
    {
        $searchCutLength = 200;

        $textValue = html_entity_decode($this->getTextContent(), ENT_QUOTES, 'UTF-8');

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
        if ($this->h1 !== '') {
            return $parentElement->getTitle() . ': ' . $this->h1;
        }
        return $parentElement->getTitle() . ': ' . $this->title;
    }

    private function getFormattedContent(array $stripTags): string
    {
        $originalContent = $this->content;

        $content = HtmlTagsStripper::stripTags($originalContent, $stripTags);
        $content = str_replace(["\n\r", "\r\n", "\r"], "\n", $content);
        return $content;
    }

    public function getTextContent(): string
    {
        return html_entity_decode($this->getFormattedContent(['div', 'p', 'span', 'img', 'br']));
    }

    public function getAITextContent(): string
    {
        $content = $this->getTextContent();
        return str_replace(["-\n", "\r"], '', $content);
    }

    public function getWrappedContent(): string
    {
        return str_replace("\n", '<br />', $this->getFormattedContent(['div', 'p', 'span']));
    }

    public function getSorted($array)
    {
        usort($array, function ($a, $b) {
            return strcmp($a->getTitle(), $b->getTitle());
        });
        return $array;
    }
}
