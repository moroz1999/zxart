<?php

declare(strict_types=1);

use ZxArt\Telegram\PostService;

/** @noinspection PhpInternalEntityUsedInspection */

class TelegramServiceContainer extends DependencyInjectionServiceContainer
{
    #[Override]
    public function makeInstance(): PostService
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->registry->getService('ConfigManager');
        $config = $configManager->getConfig('telegram');

        return new PostService(
            token: (string)$config?->get('token'),
            botName: (string)$config?->get('bot_name'),
            channelId: (string)$config?->get('channel_id')
        );
    }

    #[Override]
    public function makeInjections(mixed $instance): void
    {
    }
}
