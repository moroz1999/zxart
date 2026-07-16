<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Throwable;
use ZxArt\Users\UserProfileService;

/**
 * Self-service profile endpoint for the current user (`/profile-data/`).
 * GET returns the editable profile; POST `?action=save` persists it. Anonymous
 * users get 401. Mirrors {@see Currentuser}. Named *ProfileData* so the `/profile`
 * URL stays free for the SPA page.
 */
class ProfileData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly UserProfileService $userProfileService,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        try {
            if ($this->getParameter('action') === 'save') {
                $body = json_decode((string)file_get_contents('php://input'), true);
                $profile = $this->userProfileService->saveProfile(is_array($body) ? $body : []);
            } else {
                $profile = $this->userProfileService->getProfile();
            }

            if ($profile === null) {
                $this->assignError('Unauthorized', 401);
            } else {
                $this->renderer->assign('body', $profile);
            }
        } catch (Throwable $e) {
            $this->logThrowable('Profile::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    public function getUrlName(): string
    {
        return '';
    }
}
