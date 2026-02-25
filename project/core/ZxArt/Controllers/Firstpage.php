<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use ConfigManager;
use controller;
use controllerApplication;
use ErrorLog;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Firstpage\FirstpageViewAllLinksService;
use ZxArt\Parties\Dto\PartyDto;
use ZxArt\Parties\Rest\PartyRestDto;
use ZxArt\Parties\Services\PartiesService;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Pictures\Services\PicturesService;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Rest\ProdRestDto;
use ZxArt\Prods\Services\FirstpageProdsService;
use ZxArt\Releases\Services\ReleasesService;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;
use ZxArt\Tunes\Services\TunesService;

class Firstpage extends controllerApplication
{
    public $rendererName = 'json';
    protected ObjectMapper $objectMapper;
    protected FirstpageProdsService $prodsService;
    protected PicturesService $picturesService;
    protected TunesService $tunesService;
    protected PartiesService $partiesService;
    protected ReleasesService $releasesService;
    protected FirstpageViewAllLinksService $viewAllLinksService;

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
        $this->objectMapper = new ObjectMapper();

        try {
            $configManager = $this->getService(ConfigManager::class);
            $structureManager = $this->getService(
                'structureManager',
                [
                    'rootUrl' => controller::getInstance()->rootURL,
                    'rootMarker' => $configManager->get('main.rootMarkerPublic'),
                ],
                true
            );
            $languagesManager = $this->getService(LanguagesManager::class);
            $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

            $this->prodsService = $this->getService(FirstpageProdsService::class);
            $this->picturesService = $this->getService(PicturesService::class);
            $this->tunesService = $this->getService(TunesService::class);
            $this->partiesService = $this->getService(PartiesService::class);
            $this->releasesService = $this->getService(ReleasesService::class);
            $this->viewAllLinksService = $this->getService(FirstpageViewAllLinksService::class);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Firstpage::initialize',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            throw $e;
        }
    }

    public function execute($controller): void
    {
        $action = $this->getParameter('action');

        try {
            match ($action) {
                'newProds' => $this->handleNewProds(),
                'newPictures' => $this->handleNewPictures(),
                'newTunes' => $this->handleNewTunes(),
                'bestNewDemos' => $this->handleBestNewDemos(),
                'bestNewGames' => $this->handleBestNewGames(),
                'recentParties' => $this->handleRecentParties(),
                'bestPicturesOfMonth' => $this->handleBestPicturesOfMonth(),
                'latestAddedProds' => $this->handleLatestAddedProds(),
                'latestAddedReleases' => $this->handleLatestAddedReleases(),
                'supportProds' => $this->handleSupportProds(),
                'unvotedPictures' => $this->handleUnvotedPictures(),
                'randomGoodPictures' => $this->handleRandomGoodPictures(),
                'unvotedTunes' => $this->handleUnvotedTunes(),
                'randomGoodTunes' => $this->handleRandomGoodTunes(),
                'catalogueBaseUrls' => $this->handleCatalogueBaseUrls(),
                default => $this->assignError('Unknown action: ' . ($action ?? 'null')),
            };
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Firstpage::execute[' . ($action ?? 'null') . ']',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    protected function handleNewProds(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $minRating = $this->getFloatParam('minRating', 0.0);
        $dtos = $this->prodsService->getNewProds($limit, $minRating);
        $this->assignSuccess($this->mapProds($dtos));
    }

    protected function handleNewPictures(): void
    {
        $limit = $this->getIntParam('limit', 12);
        $dtos = $this->picturesService->getNew($limit);
        $this->assignSuccess($this->mapPictures($dtos));
    }

    protected function handleNewTunes(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $dtos = $this->tunesService->getNew($limit);
        $this->assignSuccess($this->mapTunes($dtos));
    }

    protected function handleBestNewDemos(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $minRating = $this->getFloatParam('minRating', 4.0);
        $dtos = $this->prodsService->getBestNewDemos($limit, $minRating);
        $this->assignSuccess($this->mapProds($dtos));
    }

    protected function handleBestNewGames(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $minRating = $this->getFloatParam('minRating', 4.0);
        $dtos = $this->prodsService->getBestNewGames($limit, $minRating);
        $this->assignSuccess($this->mapProds($dtos));
    }

    protected function handleRecentParties(): void
    {
        $limit = $this->getIntParam('limit', 5);
        $dtos = $this->partiesService->getRecent($limit);
        $restDtos = array_map(
            fn(PartyDto $dto) => $this->objectMapper->map($dto, PartyRestDto::class),
            $dtos
        );
        $this->assignSuccess($restDtos);
    }

    protected function handleBestPicturesOfMonth(): void
    {
        $limit = $this->getIntParam('limit', 12);
        $dtos = $this->picturesService->getBestOfMonth($limit);
        $this->assignSuccess($this->mapPictures($dtos));
    }

    protected function handleLatestAddedProds(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $dtos = $this->prodsService->getLatestAdded($limit);
        $this->assignSuccess($this->mapProds($dtos));
    }

    protected function handleLatestAddedReleases(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $dtos = $this->releasesService->getLatestAddedAsProds($limit);
        $this->assignSuccess($this->mapProds($dtos));
    }

    protected function handleSupportProds(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $dtos = $this->prodsService->getForSaleOrDonation($limit);
        $this->assignSuccess($this->mapProds($dtos));
    }

    protected function handleUnvotedPictures(): void
    {
        $limit = $this->getIntParam('limit', 12);
        $dtos = $this->picturesService->getUnvotedByCurrentUser($limit);
        $this->assignSuccess($this->mapPictures($dtos));
    }

    protected function handleRandomGoodPictures(): void
    {
        $limit = $this->getIntParam('limit', 12);
        $dtos = $this->picturesService->getRandomGood($limit);
        $this->assignSuccess($this->mapPictures($dtos));
    }

    protected function handleUnvotedTunes(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $dtos = $this->tunesService->getUnvotedByCurrentUser($limit);
        $this->assignSuccess($this->mapTunes($dtos));
    }

    protected function handleRandomGoodTunes(): void
    {
        $limit = $this->getIntParam('limit', 10);
        $dtos = $this->tunesService->getRandomGood($limit);
        $this->assignSuccess($this->mapTunes($dtos));
    }

    protected function handleCatalogueBaseUrls(): void
    {
        $this->assignSuccess($this->viewAllLinksService->getCatalogueBaseUrls());
    }

    /**
     * @param ProdDto[] $dtos
     * @return ProdRestDto[]
     */
    private function mapProds(array $dtos): array
    {
        return array_map(
            fn(ProdDto $dto) => $this->objectMapper->map($dto, ProdRestDto::class),
            $dtos
        );
    }

    /**
     * @param PictureDto[] $dtos
     * @return PictureRestDto[]
     */
    private function mapPictures(array $dtos): array
    {
        return array_map(
            fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class),
            $dtos
        );
    }

    /**
     * @param TuneDto[] $dtos
     * @return TuneRestDto[]
     */
    private function mapTunes(array $dtos): array
    {
        return array_map(
            fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class),
            $dtos
        );
    }

    private function getIntParam(string $name, int $default): int
    {
        $value = $this->getParameter($name);
        if ($value === null || $value === '') {
            return $default;
        }
        $intValue = filter_var($value, FILTER_VALIDATE_INT);
        return $intValue !== false ? $intValue : $default;
    }

    private function getFloatParam(string $name, float $default): float
    {
        $value = $this->getParameter($name);
        if ($value === null || $value === '') {
            return $default;
        }
        $floatValue = filter_var($value, FILTER_VALIDATE_FLOAT);
        return $floatValue !== false ? $floatValue : $default;
    }

    private function assignSuccess(mixed $data): void
    {
        $this->renderer->assign('responseStatus', 'success');
        $this->renderer->assign('responseData', $data);
    }

    private function assignError(string $message): void
    {
        $this->renderer->assign('responseStatus', 'error');
        $this->renderer->assign('errorMessage', $message);
    }

    public function getUrlName()
    {
        return '';
    }
}
