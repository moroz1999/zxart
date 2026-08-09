<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use structureManager;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use ZxArt\Feedback\Dto\FeedbackRequestDto;
use ZxArt\Feedback\Exception\FeedbackException;
use ZxArt\Feedback\FeedbackService;

class FeedbackData extends LoggedControllerApplication
{
    private const string FEEDBACK_TYPE = 'feedback';

    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly FeedbackService $feedbackService,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly SerializerInterface $serializer,
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
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if ($requestMethod !== 'POST') {
                throw new FeedbackException('Method not allowed', 405);
            }
            $this->handleSubmit();
        } catch (FeedbackException $e) {
            $this->logThrowable('FeedbackData::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (SerializerException $exception) {
            $this->assignError($exception->getMessage(), 400);
        } catch (Throwable $e) {
            $this->logThrowable('FeedbackData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function handleSubmit(): void
    {
        $request = $this->readRequest();
        $elementId = (int)($this->getParameter('id') ?? 0);
        if ($elementId <= 0) {
            // No wrapper element id from the SPA: resolve the feedback form by type.
            $elementId = $this->resolveFeedbackId();
        }
        if ($elementId <= 0) {
            throw new FeedbackException('Feedback form not found', 404);
        }

        $this->feedbackService->submit($elementId, $request);
        $this->renderer->assign('body', ['success' => true]);
    }

    private function readRequest(): FeedbackRequestDto
    {
        return $this->serializer->deserialize(file_get_contents('php://input'), FeedbackRequestDto::class, 'json');
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

}
