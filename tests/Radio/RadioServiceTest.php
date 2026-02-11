<?php

declare(strict_types=1);

namespace ZxArt\Tests\Radio;

use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Radio\Dto\RadioCriteriaDto;
use ZxArt\Radio\Exception\RadioTuneNotFoundException;
use ZxArt\Radio\Services\RadioService;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Repositories\TunesRepository;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

class RadioServiceTest extends TestCase
{
    public function testGetNextTuneReturnsTuneDto(): void
    {
        $criteria = $this->makeCriteria();
        $element = $this->createMock(zxMusicElement::class);
        $tuneDto = new TuneDto(
            id: 1,
            title: 'Test',
            url: '/music/1',
            authors: [],
            format: 'pt3',
            year: null,
            votes: 0.0,
            votesAmount: 0,
            userVote: null,
            denyVoting: false,
            commentsAmount: 0,
            plays: 0,
            party: null,
            release: null,
            isPlayable: true,
            isRealtime: false,
            compo: null,
            mp3Url: null,
        );

        $repository = $this->createMock(TunesRepository::class);
        $repository->method('findRandomIdByCriteria')->with($criteria)->willReturn(1);

        $structureManager = $this->createMock(structureManager::class);
        $structureManager->method('getElementById')->with(1)->willReturn($element);

        $transformer = $this->createMock(TunesTransformer::class);
        $transformer->method('toDto')->with($element)->willReturn($tuneDto);

        $service = new RadioService($structureManager, $repository, $transformer);

        $this->assertSame($tuneDto, $service->getNextTune($criteria));
    }

    public function testGetNextTuneThrowsWhenNoTuneFound(): void
    {
        $criteria = $this->makeCriteria();
        $repository = $this->createMock(TunesRepository::class);
        $repository->method('findRandomIdByCriteria')->with($criteria)->willReturn(null);

        $service = new RadioService(
            $this->createMock(structureManager::class),
            $repository,
            $this->createMock(TunesTransformer::class),
        );

        $this->expectException(RadioTuneNotFoundException::class);
        $service->getNextTune($criteria);
    }

    private function makeCriteria(): RadioCriteriaDto
    {
        return new RadioCriteriaDto(
            minRating: null,
            maxRating: null,
            yearsInclude: [],
            yearsExclude: [],
            countriesInclude: [],
            countriesExclude: [],
            formatGroupsInclude: [],
            formatGroupsExclude: [],
            formatsInclude: [],
            formatsExclude: [],
            prodCategoriesInclude: [],
            bestVotesLimit: null,
            maxPlays: null,
            minPartyPlace: null,
            requireGame: null,
            hasParty: null,
            notVotedByUserId: null,
        );
    }
}
