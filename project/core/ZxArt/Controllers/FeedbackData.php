<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use structureManager;
use Throwable;
use ZxArt\Feedback\Exception\FeedbackException;
use ZxArt\Feedback\FeedbackService;

class FeedbackData extends LoggedControllerApplication
{
    private const int MAX_NAME_LENGTH = 255;
    private const int MAX_MESSAGE_LENGTH = 10000;
    private const string FEEDBACK_TYPE = 'feedback';

    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly FeedbackService $feedbackService,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    public function execute($controller): void
    {
        try {
            $requestMethod = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');
            if ($requestMethod !== 'POST') {
                throw new FeedbackException('Method not allowed', 405);
            }
            $this->handleSubmit();
        } catch (FeedbackException $e) {
            $this->logThrowable('FeedbackData::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('FeedbackData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function handleSubmit(): void
    {
        $payload = $this->getRequestPayload();
        $elementId = (int)($this->getParameter('id') ?? 0);
        if ($elementId <= 0) {
            // No wrapper element id from the SPA: resolve the feedback form by type.
            $elementId = $this->resolveFeedbackId();
        }
        if ($elementId <= 0) {
            throw new FeedbackException('Feedback form not found', 404);
        }

        $name = $this->extractString($payload, 'name', self::MAX_NAME_LENGTH);
        $email = $this->extractString($payload, 'email', self::MAX_NAME_LENGTH);
        $message = $this->extractString($payload, 'message', self::MAX_MESSAGE_LENGTH);

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new FeedbackException('Invalid email address', 400);
        }

        $this->feedbackService->submit($elementId, $name, $email, $message);
        $this->renderer->assign('body', ['success' => true]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractString(array $payload, string $key, int $maxLength): string
    {
        $value = $payload[$key] ?? null;
        if (!is_string($value)) {
            throw new FeedbackException('Missing required field: ' . $key, 400);
        }

        $value = trim($value);
        if ($value === '') {
            throw new FeedbackException('Empty required field: ' . $key, 400);
        }

        return mb_substr($value, 0, $maxLength);
    }

    /** Resolves the feedback form element id by structure type for the current language. */
    private function resolveFeedbackId(): int
    {
        $languageId = $this->languagesManager->getCurrentLanguageId();
        $elements = $this->structureManager->getElementsByType(self::FEEDBACK_TYPE, $languageId);
        $element = $elements ? reset($elements) : null;
        return $element ? (int)$element->getId() : 0;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRequestPayload(): array
    {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $_POST;
    }
}
