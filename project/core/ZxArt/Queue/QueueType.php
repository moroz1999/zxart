<?php

namespace ZxArt\Queue;

enum QueueType: string
{
    case RECALCULATION = 'recalculation';
    case AI_SEO = 'ai_seo';
    case AI_INTRO = 'ai_intro';
    case AI_CATEGORIES_TAGS = 'ai_categories';
    case AI_PRESS = 'ai_press';
    case OFFLINE = 'offline';
}