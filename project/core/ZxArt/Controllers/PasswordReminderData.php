<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Users\Exception\PasswordReminderException;
use ZxArt\Users\PasswordReminderService;
use ZxArt\Users\Rest\PasswordReminderResultRestDto;

class PasswordReminderData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PasswordReminderService $passwordReminderService,
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
            $input = $this->decodeInput();
            if ($input === []) {
                throw new PasswordReminderException('password-reminder.error-invalid', 400);
            } elseif (($input['action'] ?? '') === 'reset') {
                $result = $this->passwordReminderService->reset(
                    (string)($input['email'] ?? ''),
                    (string)($input['key'] ?? ''),
                    (string)($input['password'] ?? ''),
                    (string)($input['passwordRepeat'] ?? ''),
                );
            } elseif (($input['action'] ?? '') === 'request') {
                $result = $this->passwordReminderService->request((string)($input['email'] ?? ''));
            } else {
                throw new PasswordReminderException('password-reminder.error-invalid', 400);
            }
            $this->renderer->assign(
                'body',
                $this->objectMapper->map($result, PasswordReminderResultRestDto::class),
            );
        } catch (PasswordReminderException $exception) {
            CmsHttpResponse::getInstance()->setStatusCode((string)$exception->getStatusCode());
            $this->renderer->assign('body', ['errorMessage' => $exception->getMessage()]);
        } catch (Throwable $throwable) {
            $this->logThrowable('PasswordReminderData::execute', $throwable);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'password-reminder.error-generic']);
        }

        $this->renderer->display();
    }

    /** @return array<string, mixed> */
    private function decodeInput(): array
    {
        $input = json_decode((string)file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
