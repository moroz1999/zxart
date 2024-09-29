<?php
declare(strict_types=1);

namespace ZxArt\Ai;

class TranslatorService
{
    private const MAX_TOKENS = 8000; // Максимальное количество токенов для входа

    public function __construct(
        private readonly PromptSender $promptSender,
    )
    {
    }

    public function translate(string $text, string $sourceLang, string $targetLang): ?string
    {
        $chunks = $this->splitTextIntoChunks($text);
        $translatedChunks = [];

        foreach ($chunks as $chunk) {
            $prompt = $this->createPrompt($chunk, $sourceLang, $targetLang);
            $translatedText = $this->promptSender->send($prompt, 0.5, [], [], PromptSender::MODEL_4O_MINI);
            if ($translatedText === null) {
                return null;
            }
            $translatedChunks[] = $translatedText['text'];
        }

        $finalTranslation = implode(" ", $translatedChunks);

        return $this->postProcess($finalTranslation);
    }

    private function splitTextIntoChunks(string $text): array
    {
        $sentences = explode('.', $text);
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if ($this->getTokenCount($currentChunk . $sentence) > self::MAX_TOKENS) {
                $chunks[] = $currentChunk;
                $currentChunk = $sentence . '.';
            } else {
                $currentChunk .= $sentence . '.';
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    private function createPrompt(string $textChunk, string $sourceLang, string $targetLang): string
    {
        return "Переведи текст с {$sourceLang} на {$targetLang} без добавления каких-либо дополнительных комментариев или текста. 
СОХРАНИ оригинальные переносы строк \\n!
Верни перевод в формате json {text: \"\"}. 
        \n\n{$textChunk}";
    }

    private function getTokenCount(string $text): float
    {
        return mb_strlen($text) / 3;
    }
}
