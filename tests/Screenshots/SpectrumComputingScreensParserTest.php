<?php

declare(strict_types=1);

namespace ZxArt\Tests\Screenshots;

use PHPUnit\Framework\TestCase;
use ZxArt\Screenshots\SpectrumComputing\SpectrumComputingScreen;
use ZxArt\Screenshots\SpectrumComputing\SpectrumComputingScreenFormat;
use ZxArt\Screenshots\SpectrumComputing\SpectrumComputingScreensParser;

class SpectrumComputingScreensParserTest extends TestCase
{
    public function testTakesLoadingAndRunningScreensInPageOrder(): void
    {
        $html = $this->row('/pub/sinclair/screens/load/m/scr/ManicMiner.scr', 'Loading screen')
            . $this->row('/pub/sinclair/screens/in-game/m/ManicMiner.gif', 'Running screen')
            . $this->row('/zxdb/sinclair/entries/0003012/ManicMiner.jpg', 'Inlay - Front');

        $screens = (new SpectrumComputingScreensParser())->parse($html);

        $this->assertEquals([
            new SpectrumComputingScreen(
                'https://spectrumcomputing.co.uk/pub/sinclair/screens/load/m/scr/ManicMiner.scr',
                SpectrumComputingScreenFormat::Scr,
            ),
            new SpectrumComputingScreen(
                'https://spectrumcomputing.co.uk/pub/sinclair/screens/in-game/m/ManicMiner.gif',
                SpectrumComputingScreenFormat::Gif,
            ),
        ], $screens);
    }

    public function testPrefersNativeScreenOverPictureOfSameScreen(): void
    {
        $html = $this->row('/pub/sinclair/screens/load/l/gif/LicenceToKill.gif', 'Loading screen')
            . $this->row('/pub/sinclair/screens/load/l/scr/LicenceToKill.scr', 'Loading screen');

        $screens = (new SpectrumComputingScreensParser())->parse($html);

        $this->assertEquals([
            new SpectrumComputingScreen(
                'https://spectrumcomputing.co.uk/pub/sinclair/screens/load/l/scr/LicenceToKill.scr',
                SpectrumComputingScreenFormat::Scr,
            ),
        ], $screens);
    }

    public function testOrdersLoadingThenOpeningThenRunningScreens(): void
    {
        $html = $this->row('/zxdb/sinclair/entries/0045719/RocketMan-RUN-1.scr', 'Running screen')
            . $this->row('/zxdb/sinclair/entries/0045719/RocketMan-OPEN-2.scr', 'Opening screen')
            . $this->row('/zxdb/sinclair/entries/0045719/RocketMan-LOAD-3.scr', 'Loading screen');

        $urls = array_map(
            static fn(SpectrumComputingScreen $screen): string => $screen->url,
            (new SpectrumComputingScreensParser())->parse($html),
        );

        $this->assertSame([
            'https://spectrumcomputing.co.uk/zxdb/sinclair/entries/0045719/RocketMan-LOAD-3.scr',
            'https://spectrumcomputing.co.uk/zxdb/sinclair/entries/0045719/RocketMan-OPEN-2.scr',
            'https://spectrumcomputing.co.uk/zxdb/sinclair/entries/0045719/RocketMan-RUN-1.scr',
        ], $urls);
    }

    public function testSkipsOtherFilesAndFormats(): void
    {
        $html = $this->row('/zxdb/sinclair/entries/0003012/ManicMiner.scr', 'Media scan')
            . $this->row('/zxdb/sinclair/entries/0045719/RocketMan-RUN-1.jpg', 'Running screen');

        $this->assertSame([], (new SpectrumComputingScreensParser())->parse($html));
    }

    private function row(string $href, string $caption): string
    {
        return '<div class="row zxdb_stripes" id="local_1">'
            . '<a download href="' . $href . '" target="_blank" >'
            . '<div class="col-sm-4"><div title="' . $caption . '"></div></div>'
            . '<div class="col-sm-2"><small>' . $caption . '</small></div>'
            . '</a></div>';
    }
}
