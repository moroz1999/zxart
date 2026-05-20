<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use JsonException;
use UnexpectedValueException;
use ZxArt\Ai\Service\PromptSender;

final readonly class CommentTranslationAiService
{
    private const int MAX_ATTEMPTS = 2;

    public function __construct(
        private PromptSender $promptSender,
    ) {
    }

    /**
     * @throws JsonException
     * @throws UnexpectedValueException
     */
    public function translate(int $commentId, string $text): CommentTranslationDto
    {
        $preparedText = $this->prepareTextForPrompt($text);
        $prompt = "Translate this user comment for ZX Spectrum archive to English, Russian and Spanish.\n\nRules:\n- ALWAYS return all three translation fields: text_en, text_ru, text_es.\n- ALWAYS return original_lang with the original language code.\n- Preserve meaning, URLs, nicknames, software titles, line breaks and tone.\n- Preserve the original line breaks in every translated field. Line breaks are represented as <br>.\n- If the original text is already in one of the target languages, copy it verbatim into that language field.\n- Translate into the other two languages.\n- Never leave fields empty.\n\nReturn ONLY valid JSON:\n{\"text_en\":\"\",\"text_ru\":\"\",\"text_es\":\"\",\"original_lang\":\"\"}\n\nComment:\n{$preparedText}";

        $lastException = null;
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                return $this->requestTranslation($commentId, $prompt);
            } catch (JsonException | UnexpectedValueException $exception) {
                $lastException = $exception;
            }
        }

        throw $lastException ?? new UnexpectedValueException('Comment translation failed');
    }

    /**
     * @throws JsonException
     * @throws UnexpectedValueException
     */
    private function requestTranslation(int $commentId, string $prompt): CommentTranslationDto
    {
        $response = $this->promptSender->send(
            $prompt,
            0.3,
            null,
            true,
            PromptSender::MODEL_4O_MINI,
            $commentId
        );

        $decoded = json_decode($response ?? '', true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new UnexpectedValueException('Comment translation response is not a valid JSON object');
        }

        $textEn = $this->restoreTextFromResponse(is_string($decoded['text_en'] ?? null) ? $decoded['text_en'] : '');
        $textRu = $this->restoreTextFromResponse(is_string($decoded['text_ru'] ?? null) ? $decoded['text_ru'] : '');
        $textEs = $this->restoreTextFromResponse(is_string($decoded['text_es'] ?? null) ? $decoded['text_es'] : '');
        $originalLanguageCode = is_string($decoded['original_lang'] ?? null) ? $decoded['original_lang'] : '';
        $this->validateTranslation($textEn, $textRu, $textEs, $originalLanguageCode);

        return new CommentTranslationDto(
            textEn: $textEn,
            textRu: $textRu,
            textEs: $textEs,
            originalLanguageCode: $originalLanguageCode,
        );
    }

    private function prepareTextForPrompt(string $text): string
    {
        return str_replace(["\r\n", "\r", "\n"], '<br>', $text);
    }

    private function restoreTextFromResponse(string $text): string
    {
        return preg_replace('/<br\s*\/?>/i', "\n", $text) ?? $text;
    }

    private function validateTranslation(string $textEn, string $textRu, string $textEs, string $originalLanguageCode): void
    {
        if (trim($originalLanguageCode) === '') {
            throw new UnexpectedValueException('Comment translation response is missing original language code');
        }

        $isMissingEnglish = $originalLanguageCode !== 'en' && trim($textEn) === '';
        $isMissingRussian = $originalLanguageCode !== 'ru' && trim($textRu) === '';
        $isMissingSpanish = $originalLanguageCode !== 'es' && trim($textEs) === '';
        if ($isMissingEnglish || $isMissingRussian || $isMissingSpanish) {
            throw new UnexpectedValueException('Comment translation response is missing one or more required languages');
        }
    }


}
