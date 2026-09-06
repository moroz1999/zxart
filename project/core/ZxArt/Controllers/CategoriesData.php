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
use ZxArt\ZxProdCategories\CategoryManageService;
use ZxArt\ZxProdCategories\Dto\CategoryDeleteDto;
use ZxArt\ZxProdCategories\Dto\CategoryNodeDto;
use ZxArt\ZxProdCategories\Dto\CategorySaveDto;
use ZxArt\ZxProdCategories\Exception\CategoryException;
use ZxArt\ZxProdCategories\Rest\CategoryNodeRestDto;

/**
 * The editable production category tree (`/categories-data/`).
 *
 * GET returns the whole tree as a depth-first list with every language's title,
 * because the management screen edits all of them at once and the tree is a few
 * hundred rows. POST `?action=create|update|delete` manages it and answers with
 * the refreshed tree. Named *CategoriesData* so `/categories` stays free.
 *
 * Reads are open: category names are public data the catalogue shows anyway.
 * A write asks for the element action it performs — `zxProdCategory/receive`
 * to save one, `zxProdCategory/delete` to remove one — the same actions the
 * Smarty forms have always been gated on, resolved on the public root because
 * that is the tree a public request compiles privileges under.
 */
class CategoriesData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly CategoryManageService $categoryManageService,
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
                    'create' => $this->categoryManageService->create($this->readSaveRequest()),
                    'update' => $this->categoryManageService->update($this->readSaveRequest()),
                    'delete' => $this->categoryManageService->delete($this->readDeleteRequest()->id),
                    default => throw new CategoryException('Unsupported category action', 400),
                };
            }

            $this->renderer->assign('body', [
                'languages' => $this->buildLanguages(),
                'categories' => $this->buildCategories(),
            ]);
        } catch (CategoryException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('CategoriesData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /**
     * @return list<CategoryNodeRestDto>
     */
    private function buildCategories(): array
    {
        return array_map(
            fn(CategoryNodeDto $node): CategoryNodeRestDto => $this->objectMapper->map($node, CategoryNodeRestDto::class),
            $this->categoryManageService->getTree(),
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
     * @throws CategoryException
     */
    private function readSaveRequest(): CategorySaveDto
    {
        return $this->deserialize(CategorySaveDto::class);
    }

    /**
     * @throws CategoryException
     */
    private function readDeleteRequest(): CategoryDeleteDto
    {
        return $this->deserialize(CategoryDeleteDto::class);
    }

    /**
     * A body that does not fit the request DTO is a bad request, not a server
     * error, so the serializer's own message is passed through as a 400 — it
     * names the offending field.
     *
     * @template T of object
     * @param class-string<T> $requestClass
     * @return T
     * @throws CategoryException
     */
    private function deserialize(string $requestClass): object
    {
        $body = file_get_contents('php://input');
        if (!is_string($body)) {
            throw new CategoryException('Request body must be a JSON object', 400);
        }

        try {
            return $this->serializer->deserialize($body, $requestClass, 'json');
        } catch (SerializerException $exception) {
            throw new CategoryException($exception->getMessage(), 400);
        }
    }

    /**
     * The element action a request performs, so the endpoint asks for exactly
     * the privilege the work needs and nothing wider.
     *
     * @throws CategoryException
     */
    private function assertMayPerform(string $action): void
    {
        $privilege = match ($action) {
            'create', 'update' => 'zxProdCategory.receive',
            'delete' => 'zxProdCategory.delete',
            default => throw new CategoryException('Unsupported category action', 400),
        };

        $rootId = $this->structureManager->getRootElementId();
        $privileges = $this->elementPrivilegesService->getPrivileges($rootId, [$privilege]);
        if (($privileges->privileges[$privilege] ?? false) !== true) {
            throw new CategoryException('Forbidden', 403);
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
