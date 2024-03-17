<?php

namespace ZxArt\Queue;

enum QueueType: string
{
    case RECALCULATION = 'recalculation';
    case AI = 'ai';
    case OFFLINE = 'offline';
}