<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use JsonException;
use UnexpectedValueException;
use ZxArt\Comments\Repositories\CommentsRepository;

/**
 * @psalm-api
 */
final readonly class CommentTranslationService
{
    private const int BATCH_SIZE = 1-0;

    public function __construct(
        private CommentsRepository $commentsRepository,
        private CommentTranslationAiService $aiService,
    ) {
    }

    /**
     * @return array{processed: int, failed: int, attempts: CommentTranslationAttemptDto[]}
     */
    public function translateNextBatch(): array
    {
        $processed = 0;
        $failed = 0;
        $attempts = [];
        $comments = $this->commentsRepository->getUntranslatedComments(self::BATCH_SIZE);

        foreach ($comments as $comment) {
            try {
                $translation = $this->aiService->translate($comment->id, $comment->text);
                $this->commentsRepository->saveTranslation($comment->id, $translation);
                $processed++;
                $attempts[] = new CommentTranslationAttemptDto(
                    commentId: $comment->id,
                    sourceText: $comment->text,
                    translation: $translation,
                    error: null,
                );
            } catch (JsonException | UnexpectedValueException $e) {
                $failed++;
                $attempts[] = new CommentTranslationAttemptDto(
                    commentId: $comment->id,
                    sourceText: $comment->text,
                    translation: null,
                    error: $e->getMessage(),
                );
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'attempts' => $attempts,
        ];
    }
}
