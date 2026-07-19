<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use EmailDispatcher;
use Illuminate\Database\Connection;
use Monolog\Logger;
use settingsManager;
use Throwable;
use translationsManager;
use Override;

class PasswordReminderData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly Connection $database,
        private readonly EmailDispatcher $emailDispatcher,
        private readonly settingsManager $settingsManager,
        private readonly translationsManager $translationsManager,
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
                $this->assignResult(false, 'password-reminder.error-invalid');
            } elseif (($input['action'] ?? '') === 'reset') {
                $this->resetPassword($input);
            } else {
                $this->requestReset($input);
            }
        } catch (Throwable $throwable) {
            $this->logThrowable('PasswordReminderData::execute', $throwable);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->assignResult(false, 'password-reminder.error-generic');
        }

        $this->renderer->display();
    }

    /** @param array<string, mixed> $input */
    private function requestReset(array $input): void
    {
        $email = trim((string)($input['email'] ?? ''));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->assignResult(false, 'password-reminder.error-email');
            return;
        }

        $user = $this->getUserByEmail($email);
        if ($user !== null) {
            $structureElement = $this->getStructureElement((int)$user['id']);
            if ($structureElement !== null) {
                $userData = $user;
                $key = md5((string)$structureElement['id'] . (string)$structureElement['dateModified']);
                $userData['link'] = $this->getPublicOrigin() . '/password-reminder?email=' .
                    rawurlencode($email) . '&key=' . rawurlencode($key);
                $this->sendEmail($email, 'email.passwordreminder_subject', $userData);
            }
        }

        $this->assignResult(true, 'password-reminder.requested');
    }

    /** @param array<string, mixed> $input */
    private function resetPassword(array $input): void
    {
        $email = trim((string)($input['email'] ?? ''));
        $key = (string)($input['key'] ?? '');
        $password = (string)($input['password'] ?? '');
        $passwordRepeat = (string)($input['passwordRepeat'] ?? '');
        if ($password === '' || $password !== $passwordRepeat) {
            $this->assignResult(false, 'password-reminder.error-password-match');
            return;
        }

        $user = $this->getUserByEmail($email);
        $structureElement = $user === null
            ? null
            : $this->getStructureElement((int)$user['id']);
        if ($user === null || $structureElement === null) {
            $this->assignResult(false, 'password-reminder.error-invalid');
            return;
        }
        $genuineKey = md5((string)$structureElement['id'] . (string)$structureElement['dateModified']);
        if ($key === '' || !hash_equals($genuineKey, $key)) {
            $this->assignResult(false, 'password-reminder.error-invalid');
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userId = (int)$user['id'];
        $this->database->transaction(function () use ($userId, $passwordHash): void {
            $this->database->table('module_user')->where('id', $userId)->update(['password' => $passwordHash]);
            $this->database->table('structure_elements')->where('id', $userId)->update(['dateModified' => time()]);
        });
        $this->sendEmail($email, 'email.passwordchanged_subject', $user);
        $this->assignResult(true, 'password-reminder.changed');
    }

    /** @param array<string, mixed> $data */
    private function sendEmail(string $email, string $subjectKey, array $data): void
    {
        $settings = $this->settingsManager->getSettingsList();
        $dispatchment = $this->emailDispatcher->getEmptyDispatchment();
        $dispatchment->setFromName((string)($settings['default_sender_name'] ?? ''));
        $dispatchment->setFromEmail((string)($settings['default_sender_email'] ?? ''));
        $dispatchment->setSubject((string)$this->translationsManager->getTranslationByName($subjectKey));
        $dispatchment->setData($data);
        $dispatchment->setDataLifeTime(60);
        $dispatchment->setReferenceId((int)($data['id'] ?? 0));
        $dispatchment->setType('password');
        $dispatchment->registerReceiver($email, null);
        $this->emailDispatcher->startDispatchment($dispatchment);
    }

    private function assignResult(bool $success, string $message): void
    {
        $this->renderer->assign('body', ['success' => $success, 'message' => $message]);
    }

    /** @return array<string, mixed> */
    private function decodeInput(): array
    {
        $input = json_decode((string)file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }

    /** @return array<string, mixed>|null */
    private function getUserByEmail(string $email): ?array
    {
        $rows = $this->database->table('module_user')->where('email', $email)->take(1)->get();
        $row = $rows[0] ?? null;
        return is_object($row) ? (array)$row : null;
    }

    /** @return array<string, mixed>|null */
    private function getStructureElement(int $userId): ?array
    {
        $rows = $this->database->table('structure_elements')->where('id', (string)$userId)->take(1)->get();
        $row = $rows[0] ?? null;
        return is_object($row) ? (array)$row : null;
    }

    private function getPublicOrigin(): string
    {
        $scheme = ($_SERVER['HTTPS'] ?? '') === 'on' ? 'https' : 'http';
        $host = (string)($_SERVER['HTTP_HOST'] ?? 'zxart.ee');
        return $scheme . '://' . $host;
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
