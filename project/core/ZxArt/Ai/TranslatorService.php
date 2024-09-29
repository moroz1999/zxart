<?php
declare(strict_types=1);

namespace ZxArt\Ai;

readonly class TranslatorService
{
    public function __construct(
        private ChunkProcessor $chunkProcessor,
    )
    {
    }

    public function translate(string $text, string $sourceLang, string $targetLang): ?string
    {
        $createPrompt = static function (string $chunk) use ($sourceLang, $targetLang): string {
            return "Переведи текст с {$sourceLang} на {$targetLang} без добавления каких-либо дополнительных комментариев или текста. 
1. СОХРАНИ оригинальные теги HTML! НЕ ПЕРЕВОДИ клички, псевдонимы и названия программ, демо.
2. НЕ добавляй новые переносы строк, не пиши НИЧЕГО, кроме переведённого текста.
3. НЕ используй python - читай внимательно и осознанно.

Текст:<pre>{$chunk}</pre>";
        };

        $processResponse = static fn(array $response): string => str_replace(
            ['```html', '```', "<pre>", "</pre>"],
            '',
            $response['text']
        );

        return $this->chunkProcessor->processText(
            $text,
            $createPrompt,
            $processResponse
        );
    }
}
