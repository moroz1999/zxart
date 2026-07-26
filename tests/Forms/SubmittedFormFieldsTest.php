<?php

declare(strict_types=1);

namespace ZxArt\Tests\Forms;

use PHPUnit\Framework\TestCase;
use ZxArt\Forms\SubmittedFormFields;

final class SubmittedFormFieldsTest extends TestCase
{
    public function testSingleFileFieldKeepsItsPropertyMap(): void
    {
        $files = [
            'name' => ['image' => 'shot.png'],
            'tmp_name' => ['image' => '/tmp/php001'],
            'error' => ['image' => 0],
        ];

        self::assertSame(
            ['image' => ['name' => 'shot.png', 'tmp_name' => '/tmp/php001', 'error' => 0]],
            SubmittedFormFields::merge($files, []),
        );
    }

    /**
     * A multi-file selector arrives grouped by property; `filesDataChunk` walks a
     * list of files instead, so each uploaded file gets its own array.
     */
    public function testMultiFileFieldIsRegroupedPerFile(): void
    {
        $files = [
            'name' => ['music' => ['a.pt3', 'b.pt3']],
            'tmp_name' => ['music' => ['/tmp/php001', '/tmp/php002']],
            'error' => ['music' => [0, 0]],
        ];

        self::assertSame(
            [
                'music' => [
                    ['name' => 'a.pt3', 'tmp_name' => '/tmp/php001', 'error' => 0],
                    ['name' => 'b.pt3', 'tmp_name' => '/tmp/php002', 'error' => 0],
                ],
            ],
            SubmittedFormFields::merge($files, []),
        );
    }

    public function testPostedValuesAreKeptAlongsideUploads(): void
    {
        $files = ['name' => ['music' => ['a.pt3']], 'tmp_name' => ['music' => ['/tmp/php001']]];
        $post = ['musicTitle' => 'Intro', 'author' => ['12', '34']];

        $fields = SubmittedFormFields::merge($files, $post);

        self::assertSame('Intro', $fields['musicTitle']);
        self::assertSame(['12', '34'], $fields['author']);
        self::assertSame([['name' => 'a.pt3', 'tmp_name' => '/tmp/php001']], $fields['music']);
    }

    public function testPostedFieldOfTheSameNameIsMergedIntoTheUpload(): void
    {
        $files = ['name' => ['image' => 'shot.png']];
        $post = ['image' => ['remove' => '1']];

        self::assertSame(
            ['image' => ['name' => 'shot.png', 'remove' => '1']],
            SubmittedFormFields::merge($files, $post),
        );
    }
}
