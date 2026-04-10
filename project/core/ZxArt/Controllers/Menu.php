<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Throwable;
use ZxArt\Menu\MenuService;

class Menu extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly MenuService $menuService,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        try {
            $lang = $this->getParameter('lang') ?: null;
            if ($lang !== null) {
                $lang = (string)$lang;
            }
            $items = $this->menuService->getMenuItems($lang);
            $this->renderer->assign('body', $items);
        } catch (Throwable $e) {
            $this->logThrowable('Menu::execute', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }
}
