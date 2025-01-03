<?php
declare(strict_types=1);

namespace ZxArt\Ai\Service;

use JsonException;
use OpenAI;
use ZxArt\Ai\Exception;
use ZxArt\Logs\Log;

class PromptSender
{
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
        float  $temperature,
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
        }
        $this->log->logMessage(errorText: $prompt, id: $id);
        $data = null;
        try {
            $config = [
                'model' => $model,
                'temperature' => $temperature,
                'top_p' => 0.95,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
            ];
            if ($useJson) {
                $config['response_format'] = ["type" => "json_object"];
            }
            $response = $client->chat()->create($config);
            $result = $response->choices[0]->message->content;

            $data = $result;

            $this->log->logMessage(errorText: $result, id: $id);
        } catch (JsonException) {
            return null;
        } catch (Exception $exception) {
            $this->log->logMessage(errorText: $exception->getMessage(), id: $id);
        }
        return $data;
    }

}