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
use ZxArt\Users\Dto\PasswordReminderRequestDto;
use ZxArt\Users\Exception\PasswordReminderException;
use ZxArt\Users\PasswordReminderAction;
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
                PasswordReminderRequestDto::class,
                'json',
            );
            $result = match ($request->action) {
                PasswordReminderAction::Request => $this->passwordReminderService->request($request->email),
                PasswordReminderAction::Reset => $this->passwordReminderService->reset(
                    $request->email,
                    $request->key,
                    $request->password,
                    $request->passwordRepeat,
                ),
            };
            $this->renderer->assign(
                'body',
                $this->objectMapper->map($result, PasswordReminderResultRestDto::class),
            );
        } catch (SerializerException $exception) {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => $exception->getMessage()]);
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

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
