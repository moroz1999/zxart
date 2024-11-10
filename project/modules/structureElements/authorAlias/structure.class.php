<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\LinkTypes;
use ZxArt\Authors\Entities\Author;

/**
 * Class authorAliasElement
 *
 * @property string $title
 * @property int $authorId
 * @property int $joinAndDelete
 */
class authorAliasElement extends structureElement implements
    CommentsHolderInterface,
    JsonDataProvider,
    Author
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
     * @psalm-param list{0: 'authorPicture'|mixed, 1?: 'authorMusic'} $types
     *
     * @return array
     */
    public function getWorksList(array $types = ['authorPicture', 'authorMusic'])
    {
        $result = [];
        foreach ($types as $type) {
            if (!isset($this->worksList[$type])) {
                $structureManager = $this->getService('structureManager');
                $this->worksList[$type] = $structureManager->getElementsChildren($this->id, null, $type);
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
            if (($authors = $cache->load()) === false) {
                if ($authorId = $this->getAuthorId()) {
                    $structureManager = $this->getService('structureManager');
                    $authorElement = $structureManager->getElementById($authorId);
                    if ($authorElement?->structureType === 'author') {
                        $this->authorElement = $structureManager->getElementById($authorId);
                    }
                }
                $cache->save([$this->authorElement]);
            } else {
                $this->authorElement = reset($authors);
            }
        }
        return $this->authorElement;
    }

    public function getAuthorId()
    {
        if (!$this->authorId) {
            $structureManager = $this->getService('structureManager');
            if ($parentElement = $structureManager->getElementsFirstParent($this->id)) {
                if ($parentElement->structureType == 'author') {
                    $this->authorId = $parentElement->id;
                }
            }
        }
        if ($this->authorId) {
            return $this->authorId;
        }
        return $this->getService('ConfigManager')->get('zx.unknownAuthorId');
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
            if ($authorShip = $authorshipRepository->getAuthorshipInfo($this->id, 'prod')) {
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
            if ($authorShip = $authorshipRepository->getAuthorshipInfo($this->id, 'release')) {
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