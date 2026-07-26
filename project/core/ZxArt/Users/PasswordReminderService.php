<?php

declare(strict_types=1);

namespace ZxArt\Users;

use controller;
use EmailDispatcher;
use Illuminate\Database\Connection;
use settingsManager;
use translationsManager;
use ZxArt\Users\Dto\PasswordReminderResultDto;
use ZxArt\Users\Exception\PasswordReminderException;

readonly class PasswordReminderService
{
    public function __construct(
        private Connection $database,
        private EmailDispatcher $emailDispatcher,
        private settingsManager $settingsManager,
        private translationsManager $translationsManager,
        private controller $controller,
        private PasswordResetTokenService $tokenService,
    ) {
    }

    public function request(string $email): PasswordReminderResultDto
    {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new PasswordReminderException('password-reminder.error-email', 422);
        }

        $user = $this->getUserByEmail($email);
        $element = $user === null ? null : $this->getStructureElement((int)$user['id']);
        if ($user !== null && $element !== null) {
            $token = $this->tokenService->create(
                (int)$user['id'],
                $email,
                (string)$user['password'],
                (int)$element['dateModified'],
            );
            $emailData = $user;
            $emailData['link'] = rtrim((string)$this->controller->baseURL, '/')
                . '/password-reminder?email=' . rawurlencode($email)
                . '&key=' . rawurlencode($token);
            $this->sendEmail($email, 'email.passwordreminder_subject', $emailData);
        }

        return new PasswordReminderResultDto(true, 'password-reminder.requested');
    }

    public function reset(
        string $email,
        string $token,
        string $password,
        string $passwordRepeat,
    ): PasswordReminderResultDto {
        $email = trim($email);
        if ($password === '' || $password !== $passwordRepeat) {
            throw new PasswordReminderException('password-reminder.error-password-match', 422);
        }

        $user = $this->getUserByEmail($email);
        $element = $user === null ? null : $this->getStructureElement((int)$user['id']);
        if (
            $user === null
            || $element === null
            || !$this->tokenService->isValid(
                $token,
                (int)$user['id'],
                $email,
                (string)$user['password'],
                (int)$element['dateModified'],
            )
        ) {
            throw new PasswordReminderException('password-reminder.error-invalid', 400);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userId = (int)$user['id'];
        $this->database->transaction(function () use ($userId, $passwordHash): void {
            $this->database->table('module_user')->where('id', $userId)->update(['password' => $passwordHash]);
            $this->database->table('structure_elements')->where('id', $userId)->update(['dateModified' => time()]);
        });
        $this->sendEmail($email, 'email.passwordchanged_subject', $user);

        return new PasswordReminderResultDto(true, 'password-reminder.changed');
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
        $dispatchment->registerReceiver($email);
        $this->emailDispatcher->startDispatchment($dispatchment);
    }

    /** @return array<string, mixed>|null */
    private function getUserByEmail(string $email): ?array
    {
        $row = $this->database->table('module_user')->where('email', $email)->first();
        return $row === null ? null : (array)$row;
    }

    /** @return array<string, mixed>|null */
    private function getStructureElement(int $userId): ?array
    {
        $row = $this->database->table('structure_elements')->where('id', $userId)->first();
        return $row === null ? null : (array)$row;
    }
}
