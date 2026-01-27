<?php

use App\Logging\EventsLog;
use App\Users\CurrentUser;

/**
 * Class commentElement
 *
 * @property string $author @deprecated use getUserElement() instead
 * @property string $email @deprecated use getUserElement() instead
 * @property string website @deprecated use getUserElement() instead
 * @property string content
 * @property string ipAddress
 * @property string targetType
 * @property int dateTime
 * @property int userId
 * @property int votes
 * @property int approved
 */
class commentElement extends structureElement implements MetadataProviderInterface, VotesHolderInterface, CommentsHolderInterface, JsonDataProvider
{
    use UserElementProviderTrait;
    use MetadataProviderTrait;
    use CommentsTrait;
    use SearchTypesProviderTrait;
    use JsonDataProviderElement;

    public const int EDIT_LIMIT = 7200;

    public $dataResourceName = 'module_comment';
    protected $allowedTypes = ['comment'];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $userVote;
    protected $replies;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['author'] = 'text';
        $moduleStructure['email'] = 'text';
        $moduleStructure['website'] = 'url';
        $moduleStructure['content'] = 'textarea';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['votes'] = 'naturalNumber';

        $moduleStructure['dateTime'] = 'dateTime';
        $moduleStructure['ipAddress'] = 'text';
        $moduleStructure['targetType'] = 'text';
        $moduleStructure['approved'] = 'checkbox';
    }

    public function getTarget(): ?structureElement
    {
        $result = null;
        if ($targetId = $this->getTargetId()) {
            $result = $this->getService('structureManager')->getElementById($targetId);
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getTargetId()
    {
        $result = 0;
        $connectedIds = $this->getService('linksManager')->getConnectedIdList($this->id, "commentTarget", "child");
        if ($connectedIds) {
            $result = $connectedIds[0];
        }
        return $result;
    }

    /**
     * return commentElement[]
     */
    public function getReplies()
    {
        return $this->getChildrenList();
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getParentElement()
    {
        $structureManager = $this->getService('structureManager');
        // First check if there is a parent comment (nested comments)
        if ($parentComment = $structureManager->getElementsFirstParent($this->id, 'structure')) {
            if ($parentComment->structureType === 'comment') {
                return $parentComment;
            }
        }
        // Fallback to commentTarget link (usually for the top-level entity)
        return $structureManager->getElementsFirstParent($this->id, 'commentTarget');
    }

    public function logCreation()
    {
        $this->getService(EventsLog::class)->logEvent($this->id, 'comment');
    }

    public function recalculateVotes()
    {
        $votesValue = 0;
        $votesManager = $this->getService('votesManager');
        $user = $this->getService(CurrentUser::class);
        if ($votesList = $votesManager->getElementVotesList($this->id)) {
            foreach ($votesList as $vote) {
                if ($vote['userId'] !== $user->id) {
                    if ($vote['value'] > 0) {
                        $votesValue++;
                    } else {
                        $votesValue--;
                    }
                }
            }
        }
        $this->votes = $votesValue;
        $this->getService('db')
            ->table($this->dataResourceName)
            ->where('id', '=', $this->getPersistedId())
            ->update(['votes' => $this->votes]);
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
        if ($this->userVote === null) {
            $votesManager = $this->getService('votesManager');
            $this->setUserVote($votesManager->getElementUserVote($this->id, $this->structureType));
        }
        return $this->userVote;
    }

    public function getAuthorName()
    {
        if ($userElement = $this->getUserElement()) {
            return $userElement->getTitle();
        }
        return $this->author;
    }

    public function isVotingDenied()
    {
        return !controller::getInstance()->getConfigManager()->get('voting.allowed.comment');
    }

    public function getInitialTarget()
    {
        $targetElement = $this->getTarget();
        if ($targetElement && $targetElement->structureType === 'comment') {
            /**
             * @var commentElement $targetElement
             */
            return $targetElement->getInitialTarget();
        }
        return $targetElement;
    }

    public function areCommentsAllowed()
    {
        if ($this->getCommentsConfig()->get($this->structureType . '.allowed')) {
            if ($target = $this->getInitialTarget()) {
                if ($target instanceof CommentsHolderInterface) {
                    return $target->areCommentsAllowed();
                }
            }
            return true;
        }
        return false;
    }

    public function getUserName()
    {
        if ($userElement = $this->getUserElement()) {
            return $userElement;
        }
        return null;
    }

    function linkifyHtml(string $html): string
    {
        return preg_replace_callback(
            '~\bhttps?://[^\s<\)\]]+~i',
            function (array $m): string {
                $url = rtrim($m[0], '.,;:!)"]');
                $host = parse_url($url, PHP_URL_HOST) ?: '';
                $attrs = str_contains($host, 'zxart') ? '' : ' target="_blank" rel="noopener"';
                return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $attrs . '>' .
                    htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</a>';
            },
            $html
        );
    }

    public function getDecoratedContent(): string
    {
        return $this->linkifyHtml($this->content);
    }

    public function getPrivileges()
    {
        $privilegesManager = $this->getService('privilegesManager');
        $privileges = $privilegesManager->getElementPrivileges($this->id);
        if (isset($privileges[$this->structureType])) {
            return $privileges[$this->structureType];
        }
        return [];
    }

    public function isEditable(): bool
    {
        return (time() - $this->getCreatedTimestamp()) < self::EDIT_LIMIT;
    }
}
