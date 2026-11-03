<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

enum ChangeKind: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Join = 'join';
    case Convert = 'convert';
    case Link = 'link';
    case Unlink = 'unlink';
    case AttachFile = 'attachFile';
    case DeleteFile = 'deleteFile';
}
