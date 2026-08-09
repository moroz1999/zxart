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
use ZxArt\Users\Dto\PasswordChangeRequestDto;
use ZxArt\Users\PasswordChangeResult;
use ZxArt\Users\Rest\UserProfileRestDto;
use ZxArt\Users\UserProfileService;

/**
 * Self-service account endpoint for the current user (`/profile-data/`).
 * GET returns the account identity; POST `?action=change-password` replaces the
 * password. Anonymous users get 401. Mirrors {@see Currentuser}. Named
 * *ProfileData* so the `/profile` URL stays free for the SPA page.
 */
class ProfileData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly UserProfileService $userProfileService,
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
            if ($this->getParameter('action') === 'change-password') {
                $this->changePassword();
            } else {
                $this->respondWithProfile();
            }
        } catch (SerializerException $exception) {
            $this->assignError($exception->getMessage(), 400);
        } catch (Throwable $e) {
            $this->logThrowable('Profile::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function changePassword(): void
    {
        $request = $this->serializer->deserialize(
            file_get_contents('php://input'),
            PasswordChangeRequestDto::class,
            'json',
        );

        $result = $this->userProfileService->changePassword(
            $request->currentPassword,
            $request->password,
            $request->passwordRepeat,
        );

        match ($result) {
            PasswordChangeResult::Changed => $this->respondWithProfile(),
            PasswordChangeResult::Unauthorized => $this->assignError('Unauthorized', 401),
            PasswordChangeResult::WrongCurrentPassword => $this->assignError('profile.error-password-wrong', 403),
            PasswordChangeResult::NewPasswordMismatch => $this->assignError('profile.error-password-match', 400),
        };
    }

    private function respondWithProfile(): void
    {
        $profile = $this->userProfileService->getProfile();
        if ($profile === null) {
            $this->assignError('Unauthorized', 401);
            return;
        }
        $this->renderer->assign('body', $this->objectMapper->map($profile, UserProfileRestDto::class));
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
