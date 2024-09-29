<?php
declare(strict_types=1);


namespace ZxArt\Ai;

use ConfigManager;
use JsonException;
use OpenAI;

class PromptSender
{
    public const MODEL_4O = 'gpt-4o';
    public const MODEL_4O_MINI = 'gpt-4o-mini';

    public function __construct(
        private readonly ConfigManager $configManager,
    ) {}


    public function send(string $prompt, float $temperature, array $prodData, ?array $imageUrls = null, $model = self::MODEL_4O): ?array
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
            $response = $client->chat()->create([
                'model' => $model,
                'response_format' => ["type" => "json_object"],
                'temperature' => $temperature,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
            ]);
            $result = $response->choices[0]->message->content;

            $data = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        } catch (Exception $exception) {
            errorLog::getInstance()?->logMessage(self::class, $exception->getMessage() . ': ' . $prodData['title']);
        }
        return $data;
    }

}