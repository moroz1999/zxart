<?php

namespace ZxArt\Queue;

enum QueueStatus: int
{
    case STATUS_TODO = 0;
    case STATUS_INPROGRESS = 1;
    case STATUS_SUCCESS = 2;
    case STATUS_FAIL = 3;
    case STATUS_SKIP = 4;

    public function toString(): string
    {
        return match ($this) {
            self::STATUS_TODO => 'todo',
            self::STATUS_INPROGRESS => 'in progress',
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAIL => 'fail',
            self::STATUS_SKIP => 'skip',
        };
    }
}