<?php

namespace ZxArt\Queue;

enum QueueType: string
{
    case RECALCULATION = 'recalculation';
    case AI_SEO = 'ai_seo';
    case AI_INTRO = 'ai_intro';
    case AI_CATEGORIES_TAGS = 'ai_categories';
    case AI_PRESS_FIX = 'ai_press_fix';
    case AI_PRESS_TRANSLATE = 'ai_press_translate';
    case AI_PRESS_PARSE = 'ai_press_parse';
    case AI_PRESS_SEO = 'ai_press_seo';
}