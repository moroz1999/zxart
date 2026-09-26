<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use PHPUnit\Framework\TestCase;
use ZxArt\Releases\ItchIo\ItchIoPageParser;
use ZxArt\Releases\ItchIo\ItchIoUpload;

class ItchIoPageParserTest extends TestCase
{
    public function testFindsUploadsWithDownloadButtons(): void
    {
        $html = '<div class="upload_list_widget base_widget" id="upload_list_1">'
            . $this->upload('<a href="javascript:void(0);" data-upload_id="17901344" class="button download_btn">Download</a>', 'slacker.tap')
            . $this->upload('<a href="javascript:void(0);" data-upload_id="17901343" class="button download_btn">Download</a>', 'README.txt')
            . '</div>';

        $this->assertEquals([
            new ItchIoUpload(17901344, 'slacker.tap', false),
            new ItchIoUpload(17901343, 'README.txt', false),
        ], (new ItchIoPageParser())->findUploads($html));
    }

    public function testMarksBuildsForPcPlatformsAsNative(): void
    {
        $html = $this->upload(
            '<a data-upload_id="18633062" href="javascript:void(0);" class="button download_btn">Download</a>',
            'Windows English v1.2 (emulator embedded)',
            '<span aria-hidden="true" class="icon icon-windows8" title="Download for Windows"></span> ',
        );

        $this->assertEquals(
            [new ItchIoUpload(18633062, 'Windows English v1.2 (emulator embedded)', true)],
            (new ItchIoPageParser())->findUploads($html),
        );
    }

    public function testReadsFileNameTheStorageSends(): void
    {
        $parser = new ItchIoPageParser();

        $this->assertSame('ZX Spectrum English.zip', $parser->findFileName('attachment; filename="ZX Spectrum English.zip"'));
        $this->assertSame('Español.tap', $parser->findFileName("attachment; filename*=UTF-8''Espa%C3%B1ol.tap"));
        $this->assertNull($parser->findFileName(''));
    }

    public function testIgnoresFilesListedWithoutDownloadButton(): void
    {
        $html = '<p>Click download now to get access to the following files:</p>'
            . $this->upload('', 'vsjo-kubikami-1.1-cs.tap');

        $this->assertSame([], (new ItchIoPageParser())->findUploads($html));
    }

    public function testFindsDownloadPageEndpointOfDonationPrompt(): void
    {
        $html = "init_ViewGame('#view_game_1', {\"generate_download_url\":\"https:\\/\\/juanma2k.itch.io\\/critical-choice\\/download_url\",\"game\":{}});";

        $this->assertSame(
            'https://juanma2k.itch.io/critical-choice/download_url',
            (new ItchIoPageParser())->findDownloadPageEndpoint($html),
        );
    }

    public function testFindsCsrfToken(): void
    {
        $html = '<meta name="csrf_token" value="WyJvQUY0Il0=.sldq+fCAXs=" />';

        $this->assertSame('WyJvQUY0Il0=.sldq+fCAXs=', (new ItchIoPageParser())->findCsrfToken($html));
    }

    private function upload(string $button, string $name, string $platforms = ''): string
    {
        return '<div class="upload">' . $button . '<div class="info_column"><div class="upload_name">'
            . '<strong title="' . $name . '" class="name">' . $name . '</strong>'
            . ' <span class="file_size"><span>11 kB</span></span>'
            . ' <span class="download_platforms">' . $platforms . '</span></div>'
            . '<div class="upload_date"><abbr title="31 July 2026 @ 22:23 UTC"><span aria-hidden="true" class="icon icon-stopwatch"></span></abbr></div>'
            . '</div></div>';
    }
}
