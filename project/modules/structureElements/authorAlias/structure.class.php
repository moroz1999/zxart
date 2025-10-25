<?php

use ZxArt\Authors\Constants;
use ZxArt\Authors\Entities\Author;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Elements\PressMentionsProvider;
use ZxArt\LinkTypes;
use ZxArt\Press\Helpers\PressMentions;

/**
 * Class authorAliasElement
 *
 * @property string $title
 * @property int $authorId
 * @property int $joinAndDelete
 * @property pressArticleElement[] $mentions
 */
class authorAliasElement extends structureElement implements
    CommentsHolderInterface,
    JsonDataProvider,
    Author,
    PressMentionsProvider
{
    use JsonDataProviderElement;
    use AuthorTrait;
    use AuthorshipProviderTrait;
    use LettersElementsListProviderTrait;
    use ImportedItemTrait;
    use CommentsTrait;
    use CacheOperatingElement;
    use PublisherProdsProvider;
    use ReleasesProvider;
    use PressMentions;

    public $dataResourceName = 'module_authoralias';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected $prods;
    protected $releases;
    protected ?authorElement $authorElement;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['startDate'] = 'date';
        $moduleStructure['endDate'] = 'date';
        $moduleStructure['authorId'] = 'text';
        $moduleStructure['displayInMusic'] = 'checkbox';
        $moduleStructure['displayInGraphics'] = 'checkbox';
        $moduleStructure['joinAndDelete'] = 'text';

        $moduleStructure['articles'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_AUTHOR->value,
                'role' => 'parent',
            ],
        ];
        $moduleStructure['mentions'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_PEOPLE->value,
                'role' => 'parent',
            ],
        ];
    }

    public function gatherAuthorNames(): array
    {
        $authorNames = [];
        if ($this->title !== '') {
            $authorNames[] = $this->title;
        }
        return $authorNames;
    }

    /**
     * @param (mixed|string)[] $types
     *
     * @return array
     */
    public function getWorksList(array $types = [LinkTypes::AUTHOR_PICTURE->value, 'authorMusic'])
    {
        $result = [];
        foreach ($types as $type) {
            if (!isset($this->worksList[$type])) {
                $structureManager = $this->getService('structureManager');
                $this->worksList[$type] = $structureManager->getElementsChildren($this->getId(), null, $type);
            }
            $result = array_merge($result, $this->worksList[$type]);
        }
        return $result;
    }

    public function getAuthorElement(): ?authorElement
    {
        if (!isset($this->authorElement)) {
            $this->authorElement = null;
            $cache = $this->getElementsListCache('a', 60 * 60 * 24);
            $authors = $cache->load();
            if (empty($authors)) {
                if ($authorId = $this->getAuthorId()) {
                    $structureManager = $this->getService('structureManager');
                    $authorElement = $structureManager->getElementById($authorId);
                    if ($authorElement?->structureType === 'author') {
                        $this->authorElement = $structureManager->getElementById($authorId);
                        $cache->save([$this->authorElement]);
                    }
                }
            } else {
                $this->authorElement = $authors[0] ?? null;
            }
        }
        return $this->authorElement;
    }

    public function getAuthorId()
    {
        if (!$this->authorId) {
            $structureManager = $this->getService('structureManager');
            if ($parentElement = $structureManager->getElementsFirstParent($this->getId())) {
                if ($parentElement->structureType == 'author') {
                    $this->authorId = $parentElement->getId();
                }
            }
        }
        if ($this->authorId) {
            return $this->authorId;
        }
        return Constants::UNKNOWN_ID;
    }

    public function __call($methodName, $arguments)
    {
        if ($authorElement = $this->getAuthorElement()) {
            if (method_exists($authorElement, $methodName)) {
                return call_user_func_array([$authorElement, $methodName], $arguments);
            }
        }
        return false;
    }

    public function getProds()
    {
        if ($this->prods === null) {
            $this->prods = [];
            $authorshipRepository = $this->getService(AuthorshipRepository::class);
            if ($authorShip = $authorshipRepository->getAuthorshipInfo($this->getId(), 'prod')) {
                foreach ($authorShip as $item) {
                    $this->prods[] = $item['prodElement'];
                }
            }
        }

        return $this->prods;
    }

    public function getReleases()
    {
        if ($this->releases === null) {
            $this->releases = [];

            $authorshipRepository = $this->getService(AuthorshipRepository::class);
            if ($authorShip = $authorshipRepository->getAuthorshipInfo($this->getId(), 'release')) {
                foreach ($authorShip as $item) {
                    $this->releases[] = $item['releaseElement'];
                }
            }
        }

        return $this->releases;
    }

    /**
     * @return array
     *
     * @psalm-return list{0?: mixed,...}
     */
    public function getGroupsList()
    {
        $groupsList = [];
        if ($authorshipInfo = $this->getAuthorshipInfo('group')) {
            foreach ($authorshipInfo as $item) {
                $groupsList[] = $item['groupElement'];
            }
        }
        return $groupsList;
    }

    public function getSearchTitle(): string
    {
        $searchTitle = $this->title;
        if ($author = $this->getAuthorElement()) {
            $searchTitle .= ' (' . $author->getTitle() . ')';
        }
        return $searchTitle;
    }

    /**
     * @return false
     */
    public function getAliasElements(): bool
    {
        return false;
    }
}