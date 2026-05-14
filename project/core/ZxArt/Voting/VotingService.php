<?php

declare(strict_types=1);

namespace ZxArt\Voting;

use App\Logging\EventsLog;
use Illuminate\Database\Connection;
use structureElement;
use VotesHolderInterface;
use votesManager;
use ZxArtItem;

readonly class VotingService
{
    private const int CommentVoteUp = 1;
    private const int CommentVoteDown = -1;
    private const int ItemVoteMin = 0;
    private const int ItemVoteMax = 5;
    private const int ObjectiveVotesCount = 10;

    public function __construct(
        private votesManager $votesManager,
        private Connection $db,
        private EventsLog $eventsLog,
    ) {
    }

    public function vote(structureElement&VotesHolderInterface $element, int $value): bool
    {
        if (!$this->isValidVote($element, $value)) {
            return false;
        }
        if ($element->isVotingDenied()) {
            return false;
        }

        $isVoteSaved = $this->votesManager->vote(
            (int)$element->getId(),
            (string)$element->structureType,
            $value,
        );
        if ($isVoteSaved !== true) {
            return false;
        }

        if ($element instanceof ZxArtItem) {
            $this->recalculateZxArtItemVotes($element);
        } else {
            $element->recalculateVotes();
        }

        $element->setUserVote($value);
        if ($element->structureType !== 'comment') {
            $this->eventsLog->logEvent((int)$element->getId(), 'vote');
        }

        return true;
    }

    public function recalculateZxArtItemVotes(ZxArtItem $element): void
    {
        if ($element->isVotingDenied()) {
            $vote = 0.0;
            $votesAmount = 0;
        } else {
            $elementVotes = $this->votesManager->getElementFilteredVotes((int)$element->id);
            $votesAmount = count($elementVotes);
            $vote = $this->calculateWeightedVote($elementVotes, $votesAmount);
        }

        $element->votes = $vote;
        $element->votesAmount = $votesAmount;

        $this->db
            ->table($element->dataResourceName)
            ->where('id', '=', $element->getPersistedId())
            ->update(['votes' => $element->votes, 'votesAmount' => $element->votesAmount]);

        foreach ($element->getAuthorsList() as $authorElement) {
            $authorElement->recalculate();
        }
    }

    private function isValidVote(structureElement $element, int $value): bool
    {
        if ($element->structureType === 'comment') {
            return $value === self::CommentVoteUp || $value === self::CommentVoteDown;
        }

        return $value >= self::ItemVoteMin && $value <= self::ItemVoteMax;
    }

    /**
     * @param list<int|float> $elementVotes
     */
    private function calculateWeightedVote(array $elementVotes, int $votesAmount): float
    {
        if ($votesAmount === 0) {
            return 0.0;
        }

        $overallAverageVote = (float)$this->votesManager->getOverallAverageVote();
        $elementAverageVote = array_sum($elementVotes) / $votesAmount;

        return ($elementAverageVote * $votesAmount + $overallAverageVote * self::ObjectiveVotesCount)
            / ($votesAmount + self::ObjectiveVotesCount);
    }
}
