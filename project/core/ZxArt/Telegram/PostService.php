<?php

declare(strict_types=1);

namespace ZxArt\Telegram;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PostService
{
    private Client $client;

    public function __construct(
        private readonly string $token,
//        private string $botName,
        private readonly string $channelId,
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.telegram.org/bot' . $this->token . '/',
            'timeout'  => 10.0,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function sendPost(PostDto $postDto): void
    {
        $text = $this->formatText($postDto);

        if ($postDto->audio !== null && $postDto->audio !== '') {
            $this->sendAudio($postDto, $text);
        } elseif ($postDto->image !== null && $postDto->image !== '') {
            $this->sendPhoto($postDto->image, $text);
        } else {
            $this->sendMessage($text);
        }
    }

    /**
     * @throws GuzzleException
     */
    private function sendAudio(PostDto $postDto, string $text): void
    {
        $audioResponse = $this->client->get($postDto->audio);
        $filename = basename(parse_url($postDto->audio, PHP_URL_PATH) ?? 'audio.mp3');

        $this->client->post('sendAudio', [
            'multipart' => [
                ['name' => 'chat_id', 'contents' => $this->channelId],
                ['name' => 'audio', 'contents' => $audioResponse->getBody(), 'filename' => $filename],
                ['name' => 'caption', 'contents' => $text],
                ['name' => 'parse_mode', 'contents' => 'HTML'],
                ['name' => 'title', 'contents' => $postDto->title],
            ],
        ]);
    }

    /**
     * @throws GuzzleException
     */
    private function sendPhoto(string $imageUrl, string $text): void
    {
        $imageResponse = $this->client->get($imageUrl);
        $filename = basename(parse_url($imageUrl, PHP_URL_PATH) ?? 'image.jpg');

        $this->client->post('sendPhoto', [
            'multipart' => [
                ['name' => 'chat_id', 'contents' => $this->channelId],
                ['name' => 'photo', 'contents' => $imageResponse->getBody(), 'filename' => $filename],
                ['name' => 'caption', 'contents' => $text],
                ['name' => 'parse_mode', 'contents' => 'HTML'],
            ],
        ]);
    }

    /**
     * @throws GuzzleException
     */
    private function sendMessage(string $text): void
    {
        $this->client->post('sendMessage', [
            'json' => [
                'chat_id' => $this->channelId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false,
            ],
        ]);
    }

    private function formatText(PostDto $postDto): string
    {
        $parts = [];
        $parts[] = "<b>" . $postDto->title . "</b>";
        if ($postDto->description !== null && $postDto->description !== '') {
            $parts[] = $postDto->description;
        }
        $parts[] = $postDto->link;

        return implode("\n\n", $parts);
    }
}
