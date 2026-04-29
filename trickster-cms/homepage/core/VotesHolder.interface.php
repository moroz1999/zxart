<?php

interface VotesHolderInterface
{
    public function recalculateVotes();

    public function setUserVote($userVote);

    public function getUserVote();

    public function isVotingDenied();
}