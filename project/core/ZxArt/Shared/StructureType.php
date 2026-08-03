<?php

declare(strict_types=1);

namespace ZxArt\Shared;

/**
 * CMS structure element type identifiers (from `structureType` property).
 *
 * Distinct from {@see EntityType} — that enum covers entity-type identifiers
 * used for authorship/ownership (e.g. 'prod', 'release', 'author'), while
 * StructureType matches the actual CMS class names ('zxProd', 'zxRelease',
 * 'pressArticle', etc.).
 *
 * Use this enum anywhere a structure type string is required, e.g.
 * {@see \privilegesManager::checkPrivilegesForAction()}.
 */
enum StructureType: string
{
    case ZxProd = 'zxProd';
    case ZxRelease = 'zxRelease';
    case ZxMusic = 'zxMusic';
    case ZxPicture = 'zxPicture';
    case PressArticle = 'pressArticle';
    case Author = 'author';
    case AuthorAlias = 'authorAlias';
    case Group = 'group';
    case GroupAlias = 'groupAlias';
    case Party = 'party';
    case Tag = 'tag';
    case Comment = 'comment';
    case User = 'user';
    case AuthorsCatalogue = 'authorsCatalogue';
    case GroupsCatalogue = 'groupsCatalogue';
    case PartiesCatalogue = 'partiesCatalogue';
    case Year = 'year';
    case ZxProdCategory = 'zxProdCategory';
    case ZxProdCategoriesCatalogue = 'zxProdCategoriesCatalogue';
    case ZxProdsUploadForm = 'zxProdsUploadForm';
    case PicturesUploadForm = 'picturesUploadForm';
    case MusicUploadForm = 'musicUploadForm';
    case PicturesCatalogue = 'picturesCatalogue';
    case MusicCatalogue = 'musicCatalogue';
    case CountriesList = 'countriesList';
    case Stats = 'stats';
    case CommentsList = 'commentsList';
    case Feedback = 'feedback';
    case Registration = 'registration';
    case UserPlaylists = 'userPlaylists';
    case Playlist = 'playlist';
    case Root = 'root';
    case Language = 'language';
}
