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
                RegistrationRequestDto::class,
                'json',
            );
            $result = $this->registrationService->register($request);
            CmsHttpResponse::getInstance()->setStatusCode('201');
            $this->renderer->assign(
                'body',
                $this->objectMapper->map($result, RegistrationResultRestDto::class),
            );
        } catch (SerializerException $exception) {
            $this->assignError($exception->getMessage(), 400);
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
