<?php

declare(strict_types=1);

namespace ZxArt\Feedback;

use EmailDispatcher;
use feedbackElement;
use settingsManager;
use structureManager;
use ZxArt\Email\EmailValidationService;
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

    public function __construct(
        private EmailValidationService $emailValidationService,
        private structureManager $structureManager,
        private settingsManager $settingsManager,
        private EmailDispatcher $emailDispatcher,
    ) {
    }

    /** @throws FeedbackException */
    public function submit(int $elementId, string $name, string $email, string $message): void
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof feedbackElement) {
            throw new FeedbackException('Unknown feedback form', 404);
        }

        if (!$this->emailValidationService->isAllowed($email)) {
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
