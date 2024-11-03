<?php

/**
 * Class authorAliasElement
 *
 * @property string $title
 * @property int $authorId
 * @property int $joinAndDelete
 */
class authorAliasElement extends structureElement implements CommentsHolderInterface, JsonDataProvider
{
    use JsonDataProviderElement;
    use Author;
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
    protected $authorElement;

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
                'linkType' => 'pressAuthor',
                'role' => 'parent',
            ],
        ];
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

    /**
     * @return authorElement|bool
     */
    public function getAuthorElement()
    {
        if ($this->authorElement === null) {
            $this->authorElement = false;
            $cache = $this->getElementsListCache('a', 60 * 60 * 24);
            if (($authors = $cache->load()) === false) {
                if ($authorId = $this->getAuthorId()) {
                    $structureManager = $this->getService('structureManager');
                    $this->authorElement = $structureManager->getElementById($authorId);
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
            /**
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');
            if ($authorShip = $authorsManager->getAuthorshipInfo($this->id, 'prod')) {
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
            /**
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');
            if ($authorShip = $authorsManager->getAuthorshipInfo($this->id, 'release')) {
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