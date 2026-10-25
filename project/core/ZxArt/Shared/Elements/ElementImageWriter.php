<?php

declare(strict_types=1);

namespace ZxArt\Shared\Elements;

use App\Paths\PathsManager;
use structureElement;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * The single image of an element with an `image` field — an author's photo,
 * a group's logo: stored in the uploads under the element's id, its name kept
 * in `originalName`. Sets the fields and leaves saving the element to the
 * caller.
 *
 * @psalm-api
 */
final readonly class ElementImageWriter
{
    public function __construct(
        private PathsManager $pathsManager,
    ) {
    }

    /**
     * @throws AttachedFileRefusedException for a file that is no picture or too big
     */
    public function check(UploadedFileDto $image): void
    {
        $size = @filesize($image->path);
        if ($size === false || $size > AttachedFileTypes::MAX_IMAGE_BYTES) {
            throw new AttachedFileRefusedException("The image {$image->fileName} is missing or too big");
        }
        $info = @getimagesize($image->path);
        if ($info === false || !in_array($info['mime'], AttachedFileTypes::IMAGES, true)) {
            throw new AttachedFileRefusedException("{$image->fileName} is no picture");
        }
    }

    /**
     * @throws AttachedFileRefusedException
     */
    public function store(structureElement $element, UploadedFileDto $image): void
    {
        $this->check($image);
        $uploadsPath = (string)$this->pathsManager->getPath('uploads');
        $this->pathsManager->ensureDirectory($uploadsPath);
        $elementId = (string)$element->getPersistedId();
        if (!copy($image->path, $uploadsPath . $elementId)) {
            throw new AttachedFileRefusedException("The image {$image->fileName} could not be stored");
        }
        ElementFields::assign($element, ['image' => $elementId, 'originalName' => $image->fileName]);
    }

    public function remove(structureElement $element): void
    {
        $path = (string)$this->pathsManager->getPath('uploads') . $element->getPersistedId();
        if (is_file($path)) {
            unlink($path);
        }
        ElementFields::assign($element, ['image' => '', 'originalName' => '']);
    }
}
