<?php

/**
 * Class ZxArtItem
 *
 * @property int $denyComments
 */
abstract class ZxArtItem extends structureElement implements MetadataProviderInterface, VotesHolderInterface,
                                                             CommentsHolderInterface, LdJsonProviderInterface
{
    use ChartDataProviderTrait;
    use AuthorElementsProviderTrait;
    use PartyElementProviderTrait;
    use LdJsonProvider;
    use CommentsTrait;
    use MetadataProviderTrait;

    protected $userVote;
    protected $authorIds;
    protected $votesList;
    protected $tagsList;
    protected $gameElement;
    protected $votesType;
    protected $authorLinkType;
    protected $partyLinkType;
    protected $sectionType;
    public $dataResourceName;

    abstract public function getFileExtension($extensionType);

    abstract public function getElementData();

    abstract protected function fileExists($extensionType);

    public function getJsonInfo()
    {
        return json_encode($this->getElementData());
    }

    public function getFileName(
        $extensionType = 'original',
        $escapeSpaces = true,
        $urlEncode = true,
        $addAuthor = true,
        $addYear = true,
        $addParty = true,
        $addPartyPlace = false,
        $addId = false
    ) {
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

    public function renewPartyLink()
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

    public function renewAuthorLink()
    {
        $linksManager = $this->getService('linksManager');

        $compiledLinks = [];
        $elementLinks = $linksManager->getElementsLinks($this->id, $this->authorLinkType, 'child');
        foreach ($elementLinks as $link) {
            $compiledLinks[$link->parentStructureId] = $link;
        }

        foreach ($this->author as $authorId) {
            if (!isset($compiledLinks[$authorId])) {
                $linksManager->linkElements($authorId, $this->id, $this->authorLinkType);
            }
            unset($compiledLinks[$authorId]);
        }

        foreach ($compiledLinks as $key => &$link) {
            $link->delete();
        }
    }

    public function makeAuthorLink($authorId)
    {
        $linksManager = $this->getService('linksManager');
        $linksManager->linkElements($authorId, $this->id, $this->authorLinkType);
    }

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
                    } else {
                        $vote['userName'] = '';
                        $vote['userUrl'] = false;
                    }
                    $vote['date'] = date('d.m.Y H:i', $vote['date']);
                    $votesHistory[] = $vote;
                }
            }
        }
        return $votesHistory;
    }

    public function updateProdLink()
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

        if (!$linkExists && $this->game != 0) {
            $linksManager->linkElements($this->game, $this->id, 'gameLink');
        }
    }

    public function updateTagsInfo()
    {
        $tagsIndex = $this->getTagsIndex();

        $updatedTagsStrings = 0;
        $amountBeforeUpdate = $this->tagsAmount;

        $tagsStrings = explode(',', $this->tagsText);
        $tagsManager = $this->getService('tagsManager');
        foreach ($tagsStrings as $tagName) {
            if ($tagElement = $tagsManager->addTag($tagName, $this->id)) {
                if (isset($tagsIndex[$tagElement->title])) {
                    unset($tagsIndex[$tagElement->title]);
                }
                $updatedTagsStrings++;
            }
        }

        $this->tagsAmount = $updatedTagsStrings;

        for ($i = 0; $i < $updatedTagsStrings - $amountBeforeUpdate; $i++) {
            $this->getService('eventsLog')->logEvent($this->id, 'tagAdded');
        }


        foreach ($tagsIndex as $tagElement) {
            $tagsManager->removeTag($tagElement->title, $this->id);
        }
    }

    public function getTagsIndex()
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

    public function updateYear()
    {
        if (!is_numeric($this->year) || $this->year < 1983) {
            if ($party = $this->getPartyElement()) {
                $this->year = $party->getYear();
            }
            if ($game = $this->getGameElement()) {
                $this->year = $game->year;
            }
        }
    }

    public function hasTag($text)
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

    public function getTagsTexts()
    {
        $tagsTexts = [];
        foreach ($this->getTagsList() as $tag) {
            $tagsTexts[] = $tag->title;
        }
        return $tagsTexts;
    }

    public function generateTagsText()
    {
        return implode(', ', $this->getTagsTexts());
    }

    public function getVotePercent()
    {
        if ($this->isVotingDenied()) {
            return $this->getUserVote() / 5 * 100;
        } else {
            return $this->votes / 5 * 100;
        }
    }

    public function getAuthorNames()
    {
        $authorNames = [];
        if ($authors = $this->getAuthorsList()) {
            foreach ($authors as $author) {
                $authorNames[] = $author->title;
            }
        }
        return $authorNames;
    }

    public function getAuthorNamesString()
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

    public function getUser()
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

    public function getGameElement()
    {
        if ($this->gameElement === null) {
            $this->gameElement = false;
            if ($this->game) {
                $this->gameElement = $this->getService('structureManager')->getElementById($this->game);
            }
        }
        return $this->gameElement;
    }

    public function recalculate()
    {
        $this->recalculateVotes();
        $this->recalculateComments();

        $this->tagsAmount = count($this->getTagsList());
        $this->persistElementData();
    }

    public function recalculateComments()
    {
        $this->commentsAmount = $this->getCommentsAmount();
        $this->getService('db')
            ->table($this->dataResourceName)
            ->where('id', '=', $this->getId())
            ->update(['commentsAmount' => $this->commentsAmount]);
    }

    public function recalculateVotes()
    {
        if ($this->isVotingDenied()) {
            $vote = 0;
            $votesAmount = 0;
        } else {
            $vote = 0;
            $votesManager = $this->getService('votesManager');
            $overallAverageVote = $votesManager->getOverallAverageVote();
            $elementAverageVote = $votesManager->getElementAverageVote($this->id);
            $votesAmount = $votesManager->getElementVotesCount($this->id);
            $objectiveVotesCount = 10;
            if ($votesAmount > 0) {
                $vote = ($elementAverageVote * $votesAmount + $overallAverageVote * $objectiveVotesCount) / ($votesAmount + $objectiveVotesCount);
            }
        }
        $this->votes = $vote;
        $this->votesAmount = $votesAmount;

        $this->getService('db')
            ->table($this->dataResourceName)
            ->where('id', '=', $this->getId())
            ->update(['votes' => $this->votes, 'votesAmount' => $this->votesAmount]);

        foreach ($this->getAuthorsList() as $authorElement) {
            $authorElement->recalculateAuthorData();
        }
    }

    public function getChartDataIds($type = null)
    {
        return [$this->id];
    }

    public function logCreation($userId = null)
    {
        $this->getService('eventsLog')->logEvent($this->id, 'add' . ucfirst($this->structureType), $userId);
    }

    public function getPartyId()
    {
        return $this->party;
    }

    public function calculateMd5($filePath)
    {
        if (is_file($filePath)) {
            $contents = file_get_contents($filePath);
            $this->md5 = md5($contents);
            return true;
        }
        return false;
    }

    public function isRealtime()
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
}