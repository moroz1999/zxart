<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use EmailDispatcher;
use privilegesManager;
use settingsManager;
use structureManager;
use translationsManager;
use userElement;
use ZxArt\Authors\Dto\ClaimApprovalDto;
use ZxArt\Authors\Exception\ClaimApprovalException;
use ZxArt\Urls\EntityUrlResolver;

/**
 * Approving an authorship claim: it points the account's `authorId` at the
 * author and tells the claimant it went through.
 *
 * The moderation link mailed by the claim itself names both sides, so the
 * approval page can be opened by anyone who received or forwarded that mail —
 * the `approveClaim` privilege on the author is what decides who may act on it.
 */
final readonly class ClaimApprovalService
{
    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private EntityUrlResolver $entityUrlResolver,
        private settingsManager $settingsManager,
        private translationsManager $translationsManager,
        private EmailDispatcher $emailDispatcher,
    ) {
    }

    public function getClaim(int $authorId, int $userId): ClaimApprovalDto
    {
        $author = $this->getAuthor($authorId);
        $user = $this->getUser($userId);

        return $this->buildDto($author, $user);
    }

    public function approve(int $authorId, int $userId): ClaimApprovalDto
    {
        $author = $this->getAuthor($authorId);
        $user = $this->getUser($userId);

        if (!$this->canApprove($authorId)) {
            throw new ClaimApprovalException('Approving this claim is forbidden', 403);
        }

        if ((int)$user->authorId !== $authorId) {
            $user->changeConnectedAuthor($authorId);
            $user->persistElementData();
            $this->sendResultEmail($author, $user);
        }

        return $this->buildDto($author, $user);
    }

    private function buildDto(authorElement $author, userElement $user): ClaimApprovalDto
    {
        $authorId = $author->getId();

        return new ClaimApprovalDto(
            authorId: $authorId,
            authorTitle: $this->decode($author->getTitle()),
            authorUrl: $this->entityUrlResolver->urlFor($author),
            userId: $user->getId(),
            userName: $this->decode($user->userName),
            canApprove: $this->canApprove($authorId),
            approved: (int)$user->authorId === $authorId,
        );
    }

    private function canApprove(int $authorId): bool
    {
        return $this->privilegesManager->checkPrivilegesForAction($authorId, 'approveClaim', 'author') === true;
    }

    private function sendResultEmail(authorElement $author, userElement $user): void
    {
        $settings = $this->settingsManager->getSettingsList();
        $dispatchment = $this->emailDispatcher->getEmptyDispatchment();
        $dispatchment->setFromName((string)($settings['default_sender_name'] ?? ''));
        $dispatchment->setFromEmail((string)($settings['default_sender_email'] ?? ''));
        $dispatchment->registerReceiver($user->email, $user->userName);
        $dispatchment->setSubject((string)$this->translationsManager->getTranslationByName('author.claimresult_subject'));
        $dispatchment->setData([
            'resultText' => $this->translationsManager->getTranslationByName('author.claimresult_success'),
            'author' => $author->getTitle(),
        ]);
        $dispatchment->setReferenceId($author->getId());
        $dispatchment->setType('authorClaimResult');
        $this->emailDispatcher->startDispatchment($dispatchment);
    }

    private function getAuthor(int $authorId): authorElement
    {
        if ($authorId <= 0) {
            throw new ClaimApprovalException('Missing required author id', 400);
        }
        $author = $this->structureManager->getElementById($authorId);
        if (!$author instanceof authorElement) {
            throw new ClaimApprovalException('Author not found', 404);
        }

        return $author;
    }

    private function getUser(int $userId): userElement
    {
        if ($userId <= 0) {
            throw new ClaimApprovalException('Missing required user id', 400);
        }
        // the users folder is outside the public tree, so a path walk never reaches it
        $user = $this->structureManager->getElementById($userId, null, true);
        if (!$user instanceof userElement) {
            throw new ClaimApprovalException('Account not found', 404);
        }

        return $user;
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
