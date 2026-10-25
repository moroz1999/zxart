<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use authorElement;
use authorsElement;
use letterElement;
use RuntimeException;
use structureManager;
use TranslitHelper;
use ZxArt\Authors\Services\ImportedAuthorMerger;
use ZxArt\Authors\Services\ImportedAuthorsFinder;
use ZxArt\Import\Authors\AuthorImportPolicy;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Imports an author from a label: the author it stands for, by import id or
 * else by name, gets what the label adds; with none, a new one is created. One
 * transaction: an import that stops halfway leaves nothing behind.
 *
 * @psalm-api
 */
final readonly class ImportAuthorHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private ImportedAuthorsFinder $importedAuthorsFinder,
        private LabelResolver $labelResolver,
        private ImportIdOperator $importIdOperator,
        private ImportedAuthorMerger $importedAuthorMerger,
        private structureManager $structureManager,
    ) {
    }

    public function handle(ImportAuthorCommand $command): ?authorElement
    {
        $label = $command->label;
        $origin = $command->origin;
        $policy = $command->policy;
        $authorId = $label->id;
        if ($authorId === null) {
            return null;
        }

        return $this->transactionRunner->run(function () use ($command, $label, $origin, $policy, $authorId) {
            $element = $this->importedAuthorsFinder->getAuthorByLabel($label, $origin);

            if ($element === null) {
                if ($resolved = $this->labelResolver->resolve($label)) {
                    $element = $resolved;
                    $this->importIdOperator->saveImportId($element->getId(), $authorId, $origin, EntityType::Author);
                    $this->importedAuthorMerger->merge($element, $label, $origin, $policy);
                } elseif ($command->createNew) {
                    $this->createAuthor($label, $origin, $policy);
                }
            } else {
                $this->importedAuthorMerger->merge($element, $label, $origin, $policy);
            }

            $this->importedAuthorsFinder->rememberAuthor($origin, $authorId, $element);
            return $element;
        });
    }

    private function createAuthor(PersonLabel $dto, ImportOrigin $origin, AuthorImportPolicy $policy): void
    {
        $title = trim($dto->name ?? '');
        $realName = trim($dto->realName ?? '');

        if ($title === '' && $realName !== '') {
            $title = $realName;
        }

        if ($title === '') {
            return;
        }

        $firstLetter = mb_strtolower(mb_substr($title, 0, 1));
        $firstLetter = mb_substr(TranslitHelper::convert($firstLetter), 0, 1);
        if (!preg_match('/[a-zA-Z]/', $firstLetter)) {
            $firstLetter = '#';
        }

        /** @var authorsElement|null $authorsElement */
        $authorsElement = $this->structureManager->getElementByMarker('authors');
        if (!$authorsElement) {
            throw new RuntimeException(
                sprintf('Authors section is not available, author "%s" cannot be created', $title)
            );
        }

        $authorLetterElement = null;
        /** @var letterElement[] $letters */
        $letters = $this->structureManager->getElementsChildren($authorsElement->getId());
        foreach ($letters as $letterElement) {
            if (mb_strtolower($letterElement->title) === $firstLetter) {
                $authorLetterElement = $letterElement;
                break;
            }
        }

        if (!$authorLetterElement) {
            throw new RuntimeException(
                sprintf('Authors section has no "%s" letter, author "%s" cannot be created', $firstLetter, $title)
            );
        }

        /** @var authorElement|null $element */
        $element = $this->structureManager->createElement('author', 'show', $authorLetterElement->getId());
        if (!$element) {
            throw new RuntimeException(
                sprintf(
                    'Author "%s" (%s import id %s) could not be created under letter element %d',
                    $title,
                    $origin->value,
                    $dto->id ?? '',
                    $authorLetterElement->getId()
                )
            );
        }

        $this->importedAuthorMerger->merge($element, $dto, $origin, $policy);

        if ($dto->id !== null) {
            $this->importIdOperator->saveImportId($element->getId(), (string)$dto->id, $origin, EntityType::Author);
        }
    }
}
