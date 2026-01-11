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
            $this->client->post('sendAudio', [
                'json' => [
                    'chat_id' => $this->channelId,
                    'audio' => $postDto->audio,
                    'caption' => $text,
                    'parse_mode' => 'HTML',
                    'title' => $postDto->title,
                ],
            ]);
        } elseif ($postDto->image !== null && $postDto->image !== '') {
            $this->client->post('sendPhoto', [
                'json' => [
                    'chat_id' => $this->channelId,
                    'photo' => $postDto->image,
                    'caption' => $text,
                    'parse_mode' => 'HTML',
                ],
            ]);
        } else {
            $this->client->post('sendMessage', [
                'json' => [
                    'chat_id' => $this->channelId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => false,
                ],
            ]);
        }
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
