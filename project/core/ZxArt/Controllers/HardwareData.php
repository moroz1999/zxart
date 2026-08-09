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
use ZxArt\Hardware\Dto\HardwareDeleteDto;
use ZxArt\Hardware\Dto\HardwareItemDto;
use ZxArt\Hardware\Dto\HardwareSaveDto;
use ZxArt\Hardware\Exception\HardwareException;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Hardware\Rest\HardwareItemRestDto;
use ZxArt\Shared\InterfaceLanguage;

/**
 * The editable hardware catalog (`/hardware-data/`).
 *
 * GET returns the whole catalog in every public language — the management form
 * edits all of them at once, and the list is ~120 rows. POST
 * `?action=create|update|delete` manages it and answers with the refreshed
 * catalog. Named *HardwareData* so `/manage/hardware` stays free for the SPA.
 *
 * Reads are open: hardware names are public data that filters and forms need
 * anyway. Writes require `editHardware` on the public root.
 */
class HardwareData extends LoggedControllerApplication
{
    private const string PRIVILEGE = 'editHardware';

    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly HardwareCatalogService $catalogService,
        private readonly ElementPrivilegesService $elementPrivilegesService,
        private readonly structureManager $structureManager,
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
                $this->assertMayEdit();
                match ($action) {
                    'create' => $this->catalogService->create($this->readSaveRequest()),
                    'update' => $this->catalogService->update($this->readSaveRequest()),
                    'delete' => $this->catalogService->delete($this->readDeleteRequest()->id),
                    default => throw new HardwareException('Unsupported hardware action', 400),
                };
            }

            $this->renderer->assign('body', [
                'languages' => array_map(
                    static fn(InterfaceLanguage $language): string => $language->value,
                    $this->catalogService->getPublicLanguages(),
                ),
                'categories' => array_map(
                    static fn(HardwareGroup $group): string => $group->value,
                    HardwareGroup::cases(),
                ),
                'items' => $this->buildItems(),
            ]);
        } catch (HardwareException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('HardwareData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /**
     * @return list<HardwareItemRestDto>
     */
    private function buildItems(): array
    {
        return array_values(array_map(
            fn(HardwareItemDto $item): HardwareItemRestDto => $this->objectMapper->map($item, HardwareItemRestDto::class),
            $this->catalogService->getItems(),
        ));
    }

    /**
     * @throws HardwareException
     */
    private function readSaveRequest(): HardwareSaveDto
    {
        return $this->deserialize(HardwareSaveDto::class);
    }

    /**
     * @throws HardwareException
     */
    private function readDeleteRequest(): HardwareDeleteDto
    {
        return $this->deserialize(HardwareDeleteDto::class);
    }

    /**
     * A body that does not fit the request DTO is a bad request, not a server
     * error, so the serializer's own message is passed through as a 400 — it
     * names the offending field.
     *
     * @template T of object
     * @param class-string<T> $requestClass
     * @return T
     * @throws HardwareException
     */
    private function deserialize(string $requestClass): object
    {
        $body = file_get_contents('php://input');
        if (!is_string($body)) {
            throw new HardwareException('Request body must be a JSON object', 400);
        }

        try {
            return $this->serializer->deserialize($body, $requestClass, 'json');
        } catch (SerializerException $exception) {
            throw new HardwareException($exception->getMessage(), 400);
        }
    }

    /**
     * @throws HardwareException
     */
    private function assertMayEdit(): void
    {
        $rootId = $this->structureManager->getRootElementId();
        $privileges = $this->elementPrivilegesService->getPrivileges($rootId, [self::PRIVILEGE]);
        if (($privileges->privileges[self::PRIVILEGE] ?? false) !== true) {
            throw new HardwareException('Forbidden', 403);
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
