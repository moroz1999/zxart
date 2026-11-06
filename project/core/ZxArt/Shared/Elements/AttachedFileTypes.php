<?php

declare(strict_types=1);

namespace ZxArt\Shared\Elements;

/**
 * The content types and sizes the file kinds of productions and releases
 * share.
 */
final class AttachedFileTypes
{
    public const string BINARY = 'application/octet-stream';
    public const array IMAGES = ['image/png', 'image/jpeg', 'image/gif'];
    public const array DOCUMENTS = ['image/png', 'image/jpeg', 'image/gif', 'application/pdf', 'text/plain'];
    public const int MAX_IMAGE_BYTES = 10 * 1024 * 1024;
    public const int MAX_DOCUMENT_BYTES = 20 * 1024 * 1024;
}
