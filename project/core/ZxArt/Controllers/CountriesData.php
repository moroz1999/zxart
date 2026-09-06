<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use structureManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use ZxArt\ElementPrivileges\ElementPrivilegesService;
use ZxArt\Forms\Dto\FormLanguageDto;
use ZxArt\Forms\FormLanguagesProvider;
use ZxArt\Forms\Rest\FormLanguageRestDto;
use ZxArt\Geo\Dto\CitySaveDto;
use ZxArt\Geo\Dto\CountrySaveDto;
use ZxArt\Geo\Dto\ManageCityDto;
use ZxArt\Geo\Dto\ManageCountryDto;
use ZxArt\Geo\Dto\PlaceDeleteDto;
use ZxArt\Geo\Exception\GeoException;
use ZxArt\Geo\PlacesManageService;
use ZxArt\Geo\Rest\ManageCityRestDto;
use ZxArt\Geo\Rest\ManageCountryRestDto;

/**
 * The editable list of countries and their cities (`/countries-data/`).
 *
 * GET returns both lists with every language's title, because the management
 * screen edits all of them at once and the two lists are a few hundred rows
 * together. POST `?action=…` manages them and answers with the refreshed lists.
 * Named *CountriesData* so `/countries` stays free.
 *
 * Reads are open: place names are public data the geo section shows anyway.
 * A write asks for the element action it performs — `country/receive`,
 * `city/delete` and so on — the same actions the Smarty forms have always been
 * gated on, resolved on the public root because that is the tree a public
 * request compiles privileges under. Countries and cities are gated apart: the
 * two are separate element types with separate privileges.
 */
class CountriesData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PlacesManageService $placesManageService,
        private readonly ElementPrivilegesService $elementPrivilegesService,
        private readonly structureManager $structureManager,
        private readonly FormLanguagesProvider $formLanguagesProvider,
        private readonly ObjectMapper $objectMapper,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            $action = (string)$this->getParameter('action');
            if ($action !== '') {
                $this->assertMayPerform($action);
                match ($action) {
                    'createCountry' => $this->placesManageService->createCountry($this->readCountryRequest()),
                    'updateCountry' => $this->placesManageService->updateCountry($this->readCountryRequest()),
                    'deleteCountry' => $this->placesManageService->deleteCountry($this->readDeleteRequest()->id),
                    'createCity' => $this->placesManageService->createCity($this->readCityRequest()),
                    'updateCity' => $this->placesManageService->updateCity($this->readCityRequest()),
                    'deleteCity' => $this->placesManageService->deleteCity($this->readDeleteRequest()->id),
                    default => throw new GeoException('Unsupported place action', 400),
                };
            }

            $this->renderer->assign('body', [
                'languages' => $this->buildLanguages(),
                'countries' => $this->buildCountries(),
                'cities' => $this->buildCities(),
            ]);
        } catch (GeoException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('CountriesData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /**
     * @return list<ManageCountryRestDto>
     */
    private function buildCountries(): array
    {
        return array_map(
            fn(ManageCountryDto $country): ManageCountryRestDto => $this->objectMapper->map(
                $country,
                ManageCountryRestDto::class,
            ),
            $this->placesManageService->getCountries(),
        );
    }

    /**
     * @return list<ManageCityRestDto>
     */
    private function buildCities(): array
    {
        return array_map(
            fn(ManageCityDto $city): ManageCityRestDto => $this->objectMapper->map($city, ManageCityRestDto::class),
            $this->placesManageService->getCities(),
        );
    }

    /**
     * @return list<FormLanguageRestDto>
     */
    private function buildLanguages(): array
    {
        return array_map(
            fn(FormLanguageDto $language): FormLanguageRestDto => $this->objectMapper->map(
                $language,
                FormLanguageRestDto::class,
            ),
            $this->formLanguagesProvider->getLanguages(),
        );
    }

    /**
     * @throws GeoException
     */
    private function readCountryRequest(): CountrySaveDto
    {
        return $this->deserialize(CountrySaveDto::class);
    }

    /**
     * @throws GeoException
     */
    private function readCityRequest(): CitySaveDto
    {
        return $this->deserialize(CitySaveDto::class);
    }

    /**
     * @throws GeoException
     */
    private function readDeleteRequest(): PlaceDeleteDto
    {
        return $this->deserialize(PlaceDeleteDto::class);
    }

    /**
     * A body that does not fit the request DTO is a bad request, not a server
     * error, so the serializer's own message is passed through as a 400 — it
     * names the offending field.
     *
     * @template T of object
     * @param class-string<T> $requestClass
     * @return T
     * @throws GeoException
     */
    private function deserialize(string $requestClass): object
    {
        $body = file_get_contents('php://input');
        if (!is_string($body)) {
            throw new GeoException('Request body must be a JSON object', 400);
        }

        try {
            return $this->serializer->deserialize($body, $requestClass, 'json');
        } catch (SerializerException $exception) {
            throw new GeoException($exception->getMessage(), 400);
        }
    }

    /**
     * The element action a request performs, so the endpoint asks for exactly
     * the privilege the work needs and nothing wider.
     *
     * @throws GeoException
     */
    private function assertMayPerform(string $action): void
    {
        $privilege = match ($action) {
            'createCountry', 'updateCountry' => 'country.receive',
            'deleteCountry' => 'country.delete',
            'createCity', 'updateCity' => 'city.receive',
            'deleteCity' => 'city.delete',
            default => throw new GeoException('Unsupported place action', 400),
        };

        $rootId = $this->structureManager->getRootElementId();
        $privileges = $this->elementPrivilegesService->getPrivileges($rootId, [$privilege]);
        if (($privileges->privileges[$privilege] ?? false) !== true) {
            throw new GeoException('Forbidden', 403);
        }
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
