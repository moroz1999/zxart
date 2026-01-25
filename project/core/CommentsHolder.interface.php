<?php

interface CommentsHolderInterface
{
    public function recalculateComments();

    public function areCommentsAllowed();
}