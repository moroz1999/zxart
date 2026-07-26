<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Registration\Dto\RegistrationRequestDto;
use ZxArt\Registration\Exception\RegistrationException;
use ZxArt\Registration\RegistrationService;
use ZxArt\Registration\Rest\RegistrationResultRestDto;

/**
 * Static self-service registration endpoint (`/register-data/`, POST). Creates a
 * new account from a fixed set of fields (no dynamic registration fields) and
 * sends the verification email, mirroring the legacy `submitRegistration` flow.
 * Named *RegisterData* so `/register` stays free for the SPA page.
 */
class RegisterData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly RegistrationService $registrationService,
        private readonly ObjectMapper $objectMapper,
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
            $body = json_decode((string)file_get_contents('php://input'), true);
            $body = is_array($body) ? $body : [];
            $result = $this->registrationService->register(new RegistrationRequestDto(
                userName: (string)($body['userName'] ?? ''),
                email: (string)($body['email'] ?? ''),
                password: (string)($body['password'] ?? ''),
                passwordRepeat: (string)($body['passwordRepeat'] ?? ''),
                fields: $body,
            ));
            CmsHttpResponse::getInstance()->setStatusCode('201');
            $this->renderer->assign(
                'body',
                $this->objectMapper->map($result, RegistrationResultRestDto::class),
            );
        } catch (RegistrationException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('RegisterData::execute', $e);
            $this->assignError('Internal server error');
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
