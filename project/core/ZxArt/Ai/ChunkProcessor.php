<?php
declare(strict_types=1);

namespace ZxArt\Ai;

use ZxArt\Ai\Service\PromptSender;

readonly class ChunkProcessor
{
    private const MAX_TOKENS = 8000;

    public function __construct(
        private PromptSender $promptSender,
    )
    {
    }

    public function processText(
        string   $text,
        callable $createPrompt,
        callable $processResponse,
        float    $temperature = 0.5,
        ?array   $imageUrls = null,
        bool     $useJson = true,
        string   $model = PromptSender::MODEL_4O_MINI,
    )
    {
        $chunks = $this->splitTextIntoChunks($text);
        $processedChunks = [];

        foreach ($chunks as $chunk) {
            $prompt = $createPrompt($chunk);
            $response = $this->promptSender->send($prompt, $temperature, $imageUrls, $useJson, $model);
            if ($response === null) {
                return null;
            }
            $processedText = $processResponse($response);
            $processedChunks[] = $processedText;
        }

        return implode("", $processedChunks);
    }

    public function processJson(
        string   $text,
        callable $createPrompt,
        callable $processResponse,
        float    $temperature = 0.5,
        ?array   $imageUrls = null,
        bool     $useJson = true,
        string   $model = PromptSender::MODEL_4O_MINI,
    ): ?array
    {
        $chunks = $this->splitTextIntoChunks($text);
        $processedChunks = [];

        foreach ($chunks as $chunk) {
            $prompt = $createPrompt($chunk);
            $response = $this->promptSender->send($prompt, $temperature, $imageUrls, $useJson, $model);
            if ($response === null) {
                return null;
            }
            $processedText = $processResponse($response);
            $processedChunks[] = $processedText;
        }

        return $processedChunks;
    }

    private function splitTextIntoChunks(string $text): array
    {
        $sentences = preg_split('/(?<=[.?!])(?=\s)/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if ($this->getTokenCount($currentChunk . $sentence) > self::MAX_TOKENS) {
                $chunks[] = $currentChunk;
                $currentChunk = $sentence;
            } else {
                $currentChunk .= $sentence;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    private function getTokenCount(string $text): float
    {
        return mb_strlen($text) / 3;
    }
}