<?php

declare(strict_types=1);

namespace ZxArt\Feedback;

use EmailDispatcher;
use feedbackElement;
use settingsManager;
use structureManager;
use ZxArt\Email\EmailValidationService;
use ZxArt\Feedback\Dto\FeedbackRequestDto;
use ZxArt\Feedback\Exception\FeedbackException;

/**
 * Handles feedback form submissions: validates the sender email, then emails the
 * message to the form's destination with the sender address set as Reply-To.
 * Submissions are not stored.
 */
final readonly class FeedbackService
{
    private const string DISPATCHMENT_TYPE = 'feedbackForm';
    private const string DEFAULT_SENDER_EMAIL = 'noreply@noreply.com';
    private const int MAX_NAME_LENGTH = 255;
    private const int MAX_MESSAGE_LENGTH = 10000;

    public function __construct(
        private EmailValidationService $emailValidationService,
        private structureManager $structureManager,
        private settingsManager $settingsManager,
        private EmailDispatcher $emailDispatcher,
    ) {
    }

    /** @throws FeedbackException */
    public function submit(int $elementId, FeedbackRequestDto $request): void
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof feedbackElement) {
            throw new FeedbackException('Unknown feedback form', 404);
        }

        $name = $this->requireText($request->name, 'name', self::MAX_NAME_LENGTH);
        $email = $this->requireText($request->email, 'email', self::MAX_NAME_LENGTH);
        $message = $this->requireText($request->message, 'message', self::MAX_MESSAGE_LENGTH);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new FeedbackException('Invalid email address', 400);
        }
        if ($this->emailValidationService->isAllowed($email) === false) {
            throw new FeedbackException('Email address rejected', 422);
        }

        $settings = $this->settingsManager->getSettingsList();
        $fromEmail = (string)($settings['default_sender_email'] ?? self::DEFAULT_SENDER_EMAIL);
        $title = (string)$element->getTitle();
        $subject = $title !== '' ? $title : 'Feedback';
        $receiverEmail = $element->getDestination() !== '' ? $element->getDestination() : $fromEmail;

        if (!$this->sendEmail($name, $email, $message, $subject, $fromEmail, $receiverEmail, $element->getId())) {
            throw new FeedbackException('Email sending failed', 500);
        }
    }

    private function requireText(string $value, string $field, int $maxLength): string
    {
        $value = trim($value);
        if ($value === '') {
            throw new FeedbackException('Empty required field: ' . $field, 400);
        }

        return mb_substr($value, 0, $maxLength);
    }

    private function sendEmail(
        string $name,
        string $email,
        string $message,
        string $subject,
        string $fromEmail,
        string $receiverEmail,
        int $referenceId,
    ): bool {
        $dispatchment = $this->emailDispatcher->getEmptyDispatchment();
        $dispatchment->setFromName($name);
        $dispatchment->setFromEmail($fromEmail);
        $dispatchment->setReplyTo($email);
        $dispatchment->setSubject($subject);
        $dispatchment->setData([
            'heading' => $subject,
            'name' => $name,
            'email' => $email,
            'message' => $message,
        ]);
        $dispatchment->setReferenceId($referenceId);
        $dispatchment->setType(self::DISPATCHMENT_TYPE);
        $dispatchment->registerReceiver($receiverEmail, null);

        return $this->emailDispatcher->startDispatchment($dispatchment);
    }
}
