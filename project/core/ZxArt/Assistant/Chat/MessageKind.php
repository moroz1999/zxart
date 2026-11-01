<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

enum MessageKind: string
{
    /** Prose of the user or of the assistant. */
    case Text = 'text';
    /** One read tool call; arguments and result in the payload. */
    case ToolCall = 'toolCall';
    /** A proposed plan; its id in the payload. */
    case Plan = 'plan';
    /** One executed action of an approved plan. */
    case PlanProgress = 'planProgress';
    /** A run that failed. */
    case Error = 'error';
}
