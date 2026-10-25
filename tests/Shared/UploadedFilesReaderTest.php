<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ZxArt\Shared\Dto\UploadedFileDto;
use ZxArt\Shared\Http\UploadedFilesReader;

/**
 * The files of a multipart request, read from what PHP puts in `$_FILES`.
 */
final class UploadedFilesReaderTest extends TestCase
{
    private UploadedFilesReader $reader;

    protected function setUp(): void
    {
        $this->reader = new UploadedFilesReader();
    }

    public function testOneFileUnderAName(): void
    {
        $files = ['image' => $this->upload('logo.png', '/tmp/php1')];

        self::assertEquals(new UploadedFileDto('/tmp/php1', 'logo.png'), $this->reader->one($files, 'image'));
        self::assertNull($this->reader->one($files, 'file'));
    }

    public function testAnEmptySlotIsNoFile(): void
    {
        $files = ['image' => ['name' => '', 'type' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0]];

        self::assertNull($this->reader->one($files, 'image'));
    }

    public function testAListOfFilesUnderAName(): void
    {
        $files = ['releaseFiles' => [
            'name' => ['alpha.tap', 'beta.tap'],
            'type' => ['application/octet-stream', 'application/octet-stream'],
            'tmp_name' => ['/tmp/php1', '/tmp/php2'],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'size' => [10, 20],
        ]];

        self::assertEquals(
            [new UploadedFileDto('/tmp/php1', 'alpha.tap'), new UploadedFileDto('/tmp/php2', 'beta.tap')],
            $this->reader->many($files, 'releaseFiles'),
        );
        self::assertSame([], $this->reader->many($files, 'other'));
    }

    public function testFilesListedByKind(): void
    {
        $files = ['files' => [
            'name' => ['screenshots' => ['a.png', 'b.png'], 'maps' => ['m.png']],
            'type' => ['screenshots' => ['image/png', 'image/png'], 'maps' => ['image/png']],
            'tmp_name' => ['screenshots' => ['/tmp/php1', '/tmp/php2'], 'maps' => ['/tmp/php3']],
            'error' => ['screenshots' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK], 'maps' => [UPLOAD_ERR_OK]],
            'size' => ['screenshots' => [1, 2], 'maps' => [3]],
        ]];

        self::assertEquals(
            [
                'screenshots' => [new UploadedFileDto('/tmp/php1', 'a.png'), new UploadedFileDto('/tmp/php2', 'b.png')],
                'maps' => [new UploadedFileDto('/tmp/php3', 'm.png')],
            ],
            $this->reader->byKind($files, 'files'),
        );
    }

    public function testAFailedUploadIsRefused(): void
    {
        $files = ['image' => ['name' => 'logo.png', 'type' => 'image/png', 'tmp_name' => '', 'error' => UPLOAD_ERR_INI_SIZE, 'size' => 0]];

        $this->expectException(InvalidArgumentException::class);

        $this->reader->one($files, 'image');
    }

    /**
     * @return array{name: string, type: string, tmp_name: string, error: int, size: int}
     */
    private function upload(string $name, string $tmpName): array
    {
        return ['name' => $name, 'type' => 'image/png', 'tmp_name' => $tmpName, 'error' => UPLOAD_ERR_OK, 'size' => 10];
    }
}
