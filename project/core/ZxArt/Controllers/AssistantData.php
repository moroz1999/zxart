<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use JsonException;
use Monolog\Logger;
use Override;
use structureManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use App\Users\CurrentUserService;
use ZxArt\Assistant\Chat\AssistantChatService;
use ZxArt\Assistant\Chat\AssistantException;
use ZxArt\Assistant\Chat\Dto\ConversationSummaryDto;
use ZxArt\Assistant\Chat\Rest\PlanIdRequestDto;
use ZxArt\Assistant\Chat\Rest\ConversationIdRequestDto;
use ZxArt\Assistant\Chat\Rest\ConversationSummaryRestDto;
use ZxArt\Assistant\Chat\Rest\ConversationRestDto;
use ZxArt\Assistant\Chat\Rest\ConversationStateRestDto;
use ZxArt\Assistant\Chat\Rest\RunStartedRestDto;
use ZxArt\Assistant\Chat\Rest\SendRequestDto;
use ZxArt\ElementPrivileges\ElementPrivilegesService;
use ZxArt\Shared\Http\ResponseCloser;

/**
 * The editors' AI assistant (`/assistant-data/`).
 *
 * `GET ?action=list` lists the user's conversations, `GET ?action=get&id=&afterSeq=`
 * answers what is new in one; `POST ?action=create|send|approvePlan|cancelPlan|stop|delete`
 * changes them. `send` and `approvePlan`
 * answer at once and then run the agent or the plan in the same process, after
 * the response is out; the client polls `get` meanwhile.
 *
 * Everything requires the `aiAssistant` privilege on the public root.
 */
class AssistantData extends LoggedControllerApplication
{
    private const string PRIVILEGE = 'aiAssistant';
    /** Longer than the agent's own limit, so it is the agent that stops a run. */
    private const int BACKGROUND_TIME_LIMIT_SECONDS = 900;

    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly AssistantChatService $chatService,
        private readonly CurrentUserService $currentUserService,
        private readonly ElementPrivilegesService $elementPrivilegesService,
        private readonly structureManager $structureManager,
        private readonly ObjectMapper $objectMapper,
        private readonly SerializerInterface $serializer,
        private readonly ResponseCloser $responseCloser,
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
        /** @var ?callable(): void $afterResponse */
        $afterResponse = null;
        try {
            $userId = $this->authorizedUserId();
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = (string)$this->getParameter('action');
            if ($method !== 'POST' && $action !== 'get' && $action !== 'list') {
                throw new AssistantException('Method not allowed', 405);
            }
            $body = match ($action) {
                'get' => $this->objectMapper->map(
                    $this->chatService->state($userId, (int)$this->getParameter('id'), (int)$this->getParameter('afterSeq')),
                    ConversationStateRestDto::class,
                ),
                'list' => array_map(
                    fn(ConversationSummaryDto $conversation): ConversationSummaryRestDto => $this->objectMapper->map($conversation, ConversationSummaryRestDto::class),
                    $this->chatService->list($userId),
                ),
                'create' => $this->objectMapper->map($this->chatService->create($userId), ConversationRestDto::class),
                'delete' => $this->delete($userId),
                'stop' => $this->stop($userId),
                'send' => $this->send($userId, $afterResponse),
                'approvePlan' => $this->approvePlan($userId, $afterResponse),
                'cancelPlan' => $this->cancelPlan($userId),
                default => throw new AssistantException('Unsupported assistant action', 400),
            };
            $this->renderer->assign('body', $body);
        } catch (AssistantException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('AssistantData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        if ($afterResponse === null) {
            $this->renderer->display();
            return;
        }
        $this->responseCloser->prepare();
        $this->renderer->display();
        $this->responseCloser->close(self::BACKGROUND_TIME_LIMIT_SECONDS);
        try {
            $afterResponse();
        } catch (Throwable $throwable) {
            $this->logThrowable('AssistantData::afterResponse', $throwable);
        }
    }

    /**
     * @return array<never, never>
     * @throws AssistantException
     */
    private function delete(int $userId): array
    {
        $this->chatService->delete($userId, $this->deserialize(ConversationIdRequestDto::class)->conversationId);

        return [];
    }

    /**
     * @return array<never, never>
     * @throws AssistantException
     */
    private function stop(int $userId): array
    {
        $this->chatService->stop($userId, $this->deserialize(ConversationIdRequestDto::class)->conversationId);

        return [];
    }

    /**
     * @param ?callable(): void $afterResponse
     * @throws AssistantException
     * @throws JsonException
     */
    private function send(int $userId, ?callable &$afterResponse): RunStartedRestDto
    {
        $request = $this->deserialize(SendRequestDto::class);
        $runId = $this->chatService->send($userId, $request->conversationId, $request->text, $request->page);
        $afterResponse = fn() => $this->chatService->continueChat($runId);

        return new RunStartedRestDto($runId);
    }

    /**
     * @return array<never, never>
     * @throws AssistantException
     * @throws JsonException
     */
    private function cancelPlan(int $userId): array
    {
        $this->chatService->cancelPlan($userId, $this->deserialize(PlanIdRequestDto::class)->planId);

        return [];
    }

    /**
     * @param ?callable(): void $afterResponse
     * @throws AssistantException
     * @throws JsonException
     */
    private function approvePlan(int $userId, ?callable &$afterResponse): RunStartedRestDto
    {
        $planId = $this->deserialize(PlanIdRequestDto::class)->planId;
        $runId = $this->chatService->approvePlan($userId, $planId);
        $afterResponse = fn() => $this->chatService->continueExecution($runId, $planId);

        return new RunStartedRestDto($runId);
    }

    /**
     * @throws AssistantException
     */
    private function authorizedUserId(): int
    {
        $user = $this->currentUserService->getCurrentUser();
        if (empty($user->id) || $user->userName === 'anonymous') {
            throw new AssistantException('Unauthorized', 401);
        }
        $rootId = $this->structureManager->getRootElementId();
        $privileges = $this->elementPrivilegesService->getPrivileges($rootId, [self::PRIVILEGE]);
        if (($privileges->privileges[self::PRIVILEGE] ?? false) !== true) {
            throw new AssistantException('Forbidden', 403);
        }

        return (int)$user->id;
    }

    /**
     * @template T of object
     * @param class-string<T> $requestClass
     * @return T
     * @throws AssistantException
     */
    private function deserialize(string $requestClass): object
    {
        $body = file_get_contents('php://input');
        if (!is_string($body) || $body === '') {
            throw new AssistantException('Request body must be a JSON object', 400);
        }
        try {
            return $this->serializer->deserialize($body, $requestClass, 'json');
        } catch (SerializerException $exception) {
            throw new AssistantException($exception->getMessage(), 400);
        }
    }

    private function assignError(string $message, int $statusCode): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
