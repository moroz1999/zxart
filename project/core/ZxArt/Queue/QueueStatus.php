<?php

namespace ZxArt\Queue;

enum QueueStatus: int
{
    case STATUS_TODO = 0;
    case STATUS_INPROGRESS = 1;
    case STATUS_SUCCESS = 2;
    case STATUS_FAIL = 3;
}