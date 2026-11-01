<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

enum MessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
}
