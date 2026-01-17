<?php

use App\Logging\EventsLog;
use ZxArt\Elements\PressMentionsProvider;
use ZxArt\Press\Helpers\PressMentions;

/**
 * Class ZxArtItem
 *
 * @property int $denyComments
 * @property int $tagsAmount
 * @property int $year
 * @property pressArticleElement[] $mentions
 */
abstract class ZxArtItem extends structureElement implements
    MetadataProviderInterface,
    VotesHolderInterface,
    CommentsHolderInterface,
    LdJsonProviderInterface,
    Recalculable,
    PressMentionsProvider
{
    use ChartDataProviderTrait;
    use AuthorElementsProviderTrait;
    use PartyElementProviderTrait;
    use LdJsonProvider;
    use CommentsTrait;
    use MetadataProviderTrait;
    use TagsHolder;
    use PressMentions;

    protected $originalAuthors;
    protected $userVote;
    protected $authorIds;
    protected $votesList;
    protected $releaseElement;
    protected $votesType;
    protected $authorLinkType;
    protected $partyLinkType;
    protected $sectionType;
    public $dataResourceName;

    abstract public function getFileExtension($extensionType);

    abstract public function getElementData();

    abstract protected function fileExists($extensionType);


    public function getJsonInfo(?string $preset = null): ?string
    {
        if ($data = $this->getElementData($preset)) {
            return json_encode($data);
        }
        return null;
    }

    /**
     * @return string
     */
    public function getFileName(
        $extensionType = 'original',
        $escapeSpaces = true,
        $urlEncode = true,
        $addAuthor = true,
        $addYear = true,
        $addParty = true,
        $addPartyPlace = false,
        $addId = false,
    )
    {
        $fileName = '';
        if ($this->fileExists($extensionType)) {
            if ($addPartyPlace) {
                $fileName .= sprintf('%03d', $this->partyplace) . ' ';
            }
            if ($addId) {
                $fileName .= sprintf('%06d', $this->id) . ' ';
            }
            if ($addAuthor && $authorNames = $this->getAuthorNames()) {
                $fileName .= implode(', ', $authorNames);
            }

            if ($fileName) {
                $fileName .= ' - ';
            }
            $title = trim(preg_replace('#[\?\/\<\>\\\:\*\|\"]*#ui', '', html_entity_decode($this->title, ENT_QUOTES)));
            if (!$title) {
                $title = $this->id;
            }
            $fileName .= $title;

            if ($addYear && $this->year > 0) {
                $fileName .= ' (' . $this->year . ')';
            }

            if ($addParty && $party = $this->getPartyElement()) {
                $fileName .= ' (' . $party->title;
                if ($this->partyplace > 0) {
                    $fileName .= ', ' . $this->partyplace;
                }
                $fileName .= ')';
            }

            $fileName .= $this->getFileExtension($extensionType);

            $fileName = str_ireplace("/", '-', $fileName);
            if ($escapeSpaces) {
                $fileName = str_ireplace(" ", '_', $fileName);
            }
            if ($urlEncode) {
                $fileName = urlencode($fileName);
            }
        }
        return $fileName;
    }


    /**
     * @param mixed $userVote
     *
     * @return void
     */
    public function setUserVote($userVote)
    {
        $this->userVote = $userVote;
    }

    /**
     * @return mixed
     */
    public function getUserVote()
    {
        if (is_null($this->userVote)) {
            $votesManager = $this->getService('votesManager');
            $this->setUserVote($votesManager->getElementUserVote($this->id, $this->votesType));
        }
        return $this->userVote;
    }

    public function renewPartyLink(): void
    {
        $linksManager = $this->getService('linksManager');

        $linkExists = false;
        if ($elementLinks = $linksManager->getElementsLinks($this->id, $this->partyLinkType, 'child')) {
            foreach ($elementLinks as $link) {
                if ($link->parentStructureId != $this->party) {
                    $link->delete();
                } else {
                    $linkExists = true;
                }
            }
        }

        if (!$linkExists && $this->party > 0) {
            $linksManager->linkElements($this->party, $this->id, $this->partyLinkType);
        }
    }

    public function getSearchTitle(): string
    {
        $searchTitle = $this->title;
        if ($this->year) {
            $searchTitle .= ' (' . $this->year . ')';
        }
        $authors = [];
        foreach ($this->getAuthorsList() as $authorElement) {
            $authors[] = $authorElement->getTitle();
        }
        if ($authors) {
            $searchTitle .= ' / ' . implode(', ', $authors);
        }

        return $searchTitle;
    }

    public function renewAuthorLink(): void
    {
        $linkType = $this->authorLinkType;

        $linksManager = $this->getService('linksManager');

        $compiledLinks = [];
        $elementLinks = $linksManager->getElementsLinks($this->id, $linkType, 'child');
        foreach ($elementLinks as $link) {
            $compiledLinks[$link->parentStructureId] = $link;
        }

        foreach ($this->author as $authorId) {
            if (!isset($compiledLinks[$authorId])) {
                $linksManager->linkElements($authorId, $this->id, $linkType);
            }
            unset($compiledLinks[$authorId]);
        }

        foreach ($compiledLinks as $link) {
            $link->delete();
        }
    }

    public function makeAuthorLink($authorId): void
    {
        $linksManager = $this->getService('linksManager');
        $linksManager->linkElements($authorId, $this->id, $this->authorLinkType);
    }

    /**
     * @return bool
     */
    public function isVotingDenied()
    {
        if ($this->denyVoting) {
            return true;
        } elseif ($authors = $this->getRealAuthorsList()) {
            foreach ($authors as $author) {
                if ($author->isVotingDenied()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function areCommentsAllowed()
    {
        if ($this->denyComments) {
            return false;
        }
        return true;
    }


    public function getVotesHistory()
    {
        static $votesHistory;
        if ($votesHistory === null && !$this->isVotingDenied()) {
            $votesHistory = [];
            $votesManager = $this->getService('votesManager');
            if ($votesList = $votesManager->getElementVotesList($this->id)) {
                $structureManager = $this->getService('structureManager');

                foreach ($votesList as $vote) {
                    if ($user = $structureManager->getElementById($vote['userId'], null, true)) {
                        $vote['userName'] = $user->userName;
                        $vote['userUrl'] = $user->getAuthorUrl();
                        $vote['userType'] = $user->getBadgeTypesString();
                    } else {
                        $vote['userName'] = '';
                        $vote['userUrl'] = false;
                        $vote['userType'] = false;
                    }
                    $vote['date'] = date('d.m.Y H:i', $vote['date']);
                    $votesHistory[] = $vote;
                }
            }
        }
        return $votesHistory;
    }

    public function updateProdLink(): void
    {
        $linksManager = $this->getService('linksManager');

        $linkExists = false;
        if ($elementLinks = $linksManager->getElementsLinks($this->id, 'gameLink', 'child')) {
            foreach ($elementLinks as $link) {
                if ($link->parentStructureId != $this->game) {
                    $link->delete();
                } else {
                    $linkExists = true;
                }
            }
        }

        if (!$linkExists && $this->game) {
            $linksManager->linkElements($this->game, $this->id, 'gameLink');
        }
    }


    public function updateYear(): void
    {
        if (!is_numeric($this->year) || $this->year < 1983) {
            if ($party = $this->getPartyElement()) {
                $this->year = $party->getYear();
            }
            if ($releaseElement = $this->getReleaseElement()) {
                $this->year = $releaseElement->year;
            }
        }
    }

    public function getVotePercent()
    {
        if ($this->isVotingDenied()) {
            return $this->getUserVote() / 5 * 100;
        } else {
            return $this->votes / 5 * 100;
        }
    }

    /**
     * @return string[]
     *
     * @psalm-return list{0?: string,...}
     */
    public function getAuthorNames(): array
    {
        $authorNames = [];
        if ($authors = $this->getAuthorsList()) {
            foreach ($authors as $author) {
                $authorNames[] = $author->title;
            }
        }
        return $authorNames;
    }

    public function getAuthorNamesString(): string
    {
        $result = '';
        if ($authorNames = $this->getAuthorNames()) {
            $result = implode(', ', $authorNames);
        }
        return $result;
    }

    public function getAuthorIds()
    {
        if ($this->authorIds === null) {
            $this->authorIds = $this->getService('linksManager')->getConnectedIdList(
                $this->id,
                $this->authorLinkType,
                'child'
            );
        }
        return $this->authorIds;
    }

    public function getUserElement()
    {
        $user = false;
        if ($this->userId) {
            $structureManager = $this->getService('structureManager');
            $user = $structureManager->getElementById($this->userId, null, true);
        }

        return $user;
    }

    public function getPlaylistIds()
    {
        $linksManager = $this->getService('linksManager');
        return $linksManager->getConnectedIdList($this->id, 'playlist', 'child');
    }

    public function getReleaseElement()
    {
        if ($this->releaseElement === null) {
            $this->releaseElement = false;
            if ($this->game) {
                $this->releaseElement = $this->getService('structureManager')->getElementById($this->game);
            }
        }
        return $this->releaseElement;
    }

    /**
     * @return void
     */
    public function recalculate()
    {
        $this->recalculateVotes();
        $this->recalculateComments();

        $this->tagsAmount = count($this->getTagsList());
        $this->persistElementData();
    }

    /**
     * @return void
     */
    public function recalculateComments()
    {
        $this->commentsAmount = $this->getCommentsAmount();
        $this->getService('db')
            ->table($this->dataResourceName)
            ->where('id', '=', $this->getPersistedId())
            ->update(['commentsAmount' => $this->commentsAmount]);
    }

    /**
     * @return void
     */
    public function recalculateVotes()
    {
        if ($this->isVotingDenied()) {
            $vote = 0;
            $votesAmount = 0;
        } else {
            $vote = 0;

            $votesManager = $this->getService('votesManager');
            $overallAverageVote = $votesManager->getOverallAverageVote();
            $elementVotes = $votesManager->getElementFilteredVotes($this->id);
            $votesAmount = count($elementVotes);
            if ($votesAmount > 0) {
                $elementAverageVote = array_sum($elementVotes) / $votesAmount;
                $objectiveVotesCount = 10;

                $vote = ($elementAverageVote * $votesAmount + $overallAverageVote * $objectiveVotesCount) / ($votesAmount + $objectiveVotesCount);
            }
        }
        $this->votes = $vote;
        $this->votesAmount = $votesAmount;

        $this->getService('db')
            ->table($this->dataResourceName)
            ->where('id', '=', $this->getPersistedId())
            ->update(['votes' => $this->votes, 'votesAmount' => $this->votesAmount]);

        foreach ($this->getAuthorsList() as $authorElement) {
            $authorElement->recalculate();
        }
    }

    /**
     * @return int[]
     *
     * @psalm-return list{int}
     */
    public function getChartDataIds($type = null)
    {
        return [$this->getId()];
    }

    public function logCreation($userId = null): void
    {
        $this->getService(EventsLog::class)->logEvent($this->id, 'add' . ucfirst($this->structureType), $userId);
    }

    public function getPartyId()
    {
        return $this->party;
    }

    public function updateMd5(string $filePath, string $fileName): bool
    {
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        if (is_file($filePath)) {
            $contents = file_get_contents($filePath);
            $md5 = md5($contents);
            $db
                ->table('files_registry')
                ->updateOrInsert(
                    ['elementId' => $this->id],
                    [
                        'md5' => $md5,
                        'parentId' => 0,
                        'elementId' => $this->id,
                        'fileName' => $fileName,
                        'size' => strlen($contents),
                        'type' => 'file',
                    ]
                );
            return true;
        }
        $db->table('files_registry')->where('elementId', '=', $this->id)->delete();
        return false;
    }

    public function isRealtime(): bool
    {
        return in_array($this->compo,
            [
                'realtime',
                'realtimec',
                'realtimeay',
                'realtimebeeper',
                'realtime_coding',
                'realtimep',
                'paintover',
                'online',
                'onlineattr',
            ]
        );
    }

    public function getYear(): int|null
    {
        return $this->year;
    }

    /**
     * returns list of authors and aliases directly connected to zxItem
     *
     * @return authorElement[]|authorAliasElement[]
     */
    public function getOriginalAuthorsList()
    {
        if ($this->originalAuthors === null) {
            $cache = $this->getElementsListCache('oal', 60 * 60);
            if (($this->originalAuthors = $cache->load()) === null) {
                $structureManager = $this->getService('structureManager');
                $this->originalAuthors = [];

                $originalAuthorIds = $this->getService('linksManager')->getConnectedIdList(
                    $this->id,
                    "originalAuthor",
                    'child'
                );

                foreach ($originalAuthorIds as $authorId) {
                    if ($author = $structureManager->getElementById($authorId)) {
                        $this->originalAuthors[] = $author;
                    }
                }

                $cache->save($this->originalAuthors);
            }
        }
        return $this->originalAuthors;
    }

    public function renewOriginalAuthorLink(): void
    {
        $linkType = "originalAuthor";

        $linksManager = $this->getService('linksManager');

        $compiledLinks = [];
        $elementLinks = $linksManager->getElementsLinks($this->id, $linkType, 'child');
        foreach ($elementLinks as $link) {
            $compiledLinks[$link->parentStructureId] = $link;
        }

        foreach ($this->originalAuthor as $authorId) {
            if (!isset($compiledLinks[$authorId])) {
                $linksManager->linkElements($authorId, $this->id, $linkType);
            }
            unset($compiledLinks[$authorId]);
        }

        foreach ($compiledLinks as $link) {
            $link->delete();
        }
    }

    protected function optimizeAliases(string $property): void
    {
        $foundParents = [];
        foreach ($this->$property as $element) {
            if ($element->structureType === 'authorAlias' && $author = $element->getAuthorElement()) {
                $foundParents[] = $author->id;
            }
            if ($element->structureType === 'groupAlias' && $group = $element->getGroupElement()) {
                $foundParents[] = $group->id;
            }
        }

        $finalList = [];
        foreach ($this->$property as $element) {
            if (!in_array($element->id, $foundParents)) {
                $finalList[] = $element;
            }
        }
        $this->$property = $finalList;
    }
}