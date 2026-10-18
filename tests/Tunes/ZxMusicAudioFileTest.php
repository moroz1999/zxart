<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tunes;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use zxMusicElement;

/**
 * A tune with an uploaded audio recording is played from that file: the site
 * neither waits for nor asks the conversion server for one.
 */
#[AllowMockObjectsWithoutExpectations]
class ZxMusicAudioFileTest extends TestCase
{
    private const string BASE_URL = 'https://zxart.ee/';

    public function testUploadedAudioIsPlayedInsteadOfTheConvertedOne(): void
    {
        $tune = $this->tune('45113_audio', 'Barbeep live.ogg', '45113_Barbeep.ogg');

        $this->assertSame(self::BASE_URL . 'file/id:45113_audio/filename:Barbeep%20live.ogg', $tune->getMp3FilePath());
    }

    public function testUploadedAudioMakesTheTunePlayable(): void
    {
        $this->assertTrue($this->tune('45113_audio', 'Barbeep.mp3', '')->isPlayable());
    }

    public function testConvertedAudioIsPlayedWithoutAnUpload(): void
    {
        $tune = $this->tune('', '', '45113_Barbeep.ogg');

        $this->assertSame('https://music.zxart.ee/music/45113_Barbeep.ogg', $tune->getMp3FilePath());
    }

    public function testTuneWithUploadedAudioIsNotSentToConversion(): void
    {
        $this->assertFalse($this->tune('45113_audio', 'Barbeep.mp3', '')->checkIfReconversionNeeded());
    }

    private function tune(string $audioFileId, string $audioFileName, string $mp3Name): zxMusicElement&MockObject
    {
        $tune = $this->createPartialMock(zxMusicElement::class, [
            'getAudioFileId',
            'getAudioFileName',
            'getConvertedAudioName',
            'isPlayingDenied',
            'getSiteBaseUrl',
        ]);
        $tune->method('getAudioFileId')->willReturn($audioFileId);
        $tune->method('getAudioFileName')->willReturn($audioFileName);
        $tune->method('getConvertedAudioName')->willReturn($mp3Name);
        $tune->method('isPlayingDenied')->willReturn(false);
        $tune->method('getSiteBaseUrl')->willReturn(self::BASE_URL);
        return $tune;
    }
}
