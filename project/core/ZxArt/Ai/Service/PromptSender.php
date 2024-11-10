<?php
declare(strict_types=1);


namespace ZxArt\Ai\Service;

use ConfigManager;
use JsonException;
use OpenAI;
use ZxArt\Ai\errorLog;
use ZxArt\Ai\Exception;

class PromptSender
{
    public const MODEL_4O = 'gpt-4o';
    public const MODEL_4O_MINI = 'gpt-4o-mini';

    public function __construct(
        private readonly ConfigManager $configManager,
    )
    {
    }


    public function send(
        string $prompt,
        float  $temperature,
        ?array $imageUrls = null,
        bool $useJson = false,
        string $model = self::MODEL_4O,
    ): ?string
    {
        $apiKey = $this->configManager->getConfig('main')->get('ai_key');
        $client = OpenAI::client($apiKey);

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
        } catch (JsonException) {
            return null;
        } catch (Exception $exception) {
            errorLog::getInstance()?->logMessage(self::class, $exception->getMessage());
        }
        return $data;
    }

}