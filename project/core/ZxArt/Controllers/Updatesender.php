<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controllerApplication;
use Override;
use ZxArt\Telegram\PostDto;
use ZxArt\Telegram\PostService;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Updatesender extends controllerApplication
{
    #[Override]
    public function initialize(): void
    {
        // No special initialization needed for now
    }

    #[Override]
    public function execute($controller): void
    {
        /** @psalm-suppress UndefinedClass, ArgumentTypeCoercion */
        $postService = $this->getService(PostService::class);

        $postDto = new PostDto(
            title: 'Test Post Title',
            link: 'https://zxart.ee/eng/graphics/authors/s/serge-smirnov/alone-in-the-dark/',
            image: 'https://zxart.ee/zximages/id=214813;type=zxpicture;zoom=3;mode=mix.png',
            description: 'This is a test post description for the Telegram service.'
        );

        try {
            $postService->sendPost($postDto);
            echo "Post sent successfully!";
        } catch (\Exception $exception) {
            echo "Failed to send post: " . $exception->getMessage();
        }
    }
}
