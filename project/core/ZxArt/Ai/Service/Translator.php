<?php
declare(strict_types=1);

namespace ZxArt\Ai\Service;

use ZxArt\Ai\ChunkProcessor;

readonly class Translator
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
4. Не переводи названия игр, журналов и имена людей. 
5. Если термин важен в оригинале (это команда в игре или в программе, или что-то похожее), то оставь не переведенным его.

Текст:<pre>{$chunk}</pre>";
        };

        $processResponse = static fn(string $response): string => str_replace(
            ['```html', '```', "<pre>", "</pre>"],
            '',
            $response
        );

        return $this->chunkProcessor->processText(
            $text,
            $createPrompt,
            $processResponse,
            0.5,
            null,
            false,
            PromptSender::MODEL_4O
        );
    }
}
