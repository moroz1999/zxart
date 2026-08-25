<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use ZxArt\Registration\Dto\EmailVerificationRequestDto;
use ZxArt\Registration\EmailVerificationService;
use ZxArt\Registration\Exception\EmailVerificationException;
use ZxArt\Registration\Rest\EmailVerificationResultRestDto;

/**
 * Applies a registration email's verification link (`/verify-email-data/`, POST).
 * Named *VerifyEmailData* so `/verify-email` stays free for the SPA page the
 * link actually points at.
 */
class VerifyEmailData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly EmailVerificationService $emailVerificationService,
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
            $request = $this->serializer->deserialize(
                file_get_contents('php://input'),
                EmailVerificationRequestDto::class,
                'json',
            );
            $result = $this->emailVerificationService->verify($request);
            $this->renderer->assign(
                'body',
                $this->objectMapper->map($result, EmailVerificationResultRestDto::class),
            );
        } catch (SerializerException $exception) {
            $this->assignError($exception->getMessage(), 400);
        } catch (EmailVerificationException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('VerifyEmailData::execute', $throwable);
            $this->assignError('verify-email.error-generic');
        }

        $this->renderer->display();
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
