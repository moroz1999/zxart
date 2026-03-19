<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use controllerApplication;
use ErrorLog;
use Throwable;
use ZxArt\Menu\MenuService;

class Menu extends controllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        private readonly MenuService $menuService,
    ) {
        parent::__construct($controller);
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
            ErrorLog::getInstance()->logMessage(
                'Menu::execute',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }
}
