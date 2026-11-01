<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Llm;

enum LlmRole: string
{
    case System = 'system';
    case User = 'user';
    case Assistant = 'assistant';
    case Tool = 'tool';
}
