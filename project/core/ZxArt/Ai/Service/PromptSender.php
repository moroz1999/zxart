<?php
declare(strict_types=1);

namespace ZxArt\Ai\Service;

use OpenAI;
use ZxArt\Ai\Exception;
use ZxArt\Logs\Log;

class PromptSender
{
    public const MODEL_O3_MINI = 'o3-mini';
    public const MODEL_4O = 'gpt-4o';
    public const MODEL_4O_MINI = 'gpt-4o-mini';

    public function __construct(
        private string $apiKey,
        private Log    $log,
    )
    {
    }


    public function send(
        string $prompt,
        ?float $temperature,
        ?array $imageUrls = null,
        bool   $useJson = false,
        string $model = self::MODEL_4O,
        ?int   $id = null,
    ): ?string
    {
        $client = OpenAI::client($this->apiKey);

        $content = [
            [
                "type" => "text",
                "text" => $prompt,
            ],
        ];
        if ($imageUrls !== null) {
            foreach ($imageUrls as $imageUrl) {
                $content[] = [
                    "type" => "image_url",
                    "image_url" => [
                        "url" => $imageUrl,
                        "detail" => "low",
                    ],
                ];
            }
            $this->log->logMessage(text: implode(',', $imageUrls), id: $id);
        }
        $this->log->logMessage(text: $prompt, id: $id);
        $result = null;
        try {
            $config = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
            ];
            if ($temperature !== null) {
                $config['temperature'] = $temperature;
            }
            if ($model !== self::MODEL_O3_MINI) {
                $config['top_p'] = 0.95;
            }
            if ($useJson) {
                $config['response_format'] = ["type" => "json_object"];
            }
            $response = $client->chat()->create($config);
            $result = $response->choices[0]->message->content;

            $logMessage = $result ?? 'Empty response received from: ' . json_encode($response, JSON_THROW_ON_ERROR);
            $this->log->logMessage(text: $logMessage, id: $id);
        } catch (Exception $exception) {
            $this->log->logMessage(text: $exception->getMessage(), id: $id);
        }
        return $result;
    }

}