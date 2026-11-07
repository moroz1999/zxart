<?php

declare(strict_types=1);

namespace ZxArt\Shared;

enum DatabaseTable: string
{
    case ActionsLog = 'actions_log';
    case AiConversation = 'ai_conversation';
    case AiMessage = 'ai_message';
    case AiPlan = 'ai_plan';
    case AiRun = 'ai_run';
    case Author = 'module_author';
    case AuthorAlias = 'module_authoralias';
    case Authorship = 'authorship';
    case City = 'module_city';
    case Comment = 'module_comment';
    case Country = 'module_country';
    case FilesRegistry = 'files_registry';
    case Generic = 'module_generic';
    case Hardware = 'hardware';
    case HardwareName = 'hardware_name';
    case ImportOrigin = 'import_origin';
    case Group = 'module_group';
    case GroupAlias = 'module_groupalias';
    case Party = 'module_party';
    case PressArticle = 'module_pressarticle';
    case StructureElements = 'structure_elements';
    case Tag = 'module_tag';
    case StructureLinks = 'structure_links';
    case User = 'module_user';
    case UserGroup = 'module_user_group';
    case VotesHistory = 'votes_history';
    case ZxMusic = 'module_zxmusic';
    case ZxPicture = 'module_zxpicture';
    case ZxProd = 'module_zxprod';
    case ZxProdCategory = 'module_zxprodcategory';
    case ZxProdHardware = 'module_zxprod_hw_required';
    case ZxRelease = 'module_zxrelease';
    case ZxReleaseFormat = 'module_zxrelease_format';
    case ZxReleaseHardware = 'module_zxrelease_hw_required';
    case ZxItemLanguage = 'zxitem_language';
}
