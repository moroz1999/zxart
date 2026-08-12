<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use Monolog\Logger;
use Throwable;

abstract class LoggedControllerApplication extends controllerApplication
{
    public function __construct(
        controller $controller,
        protected readonly Logger $logger,
    ) {
        parent::__construct($controller);
        $this->applyRequestedLanguage();
    }

    /**
     * The SPA owns the interface language and sends it as an `X-Language` header
     * (iso6393). Applying it here makes every SPA data endpoint return localized
     * content in that language, independent of the URL or session.
     */
    protected function applyRequestedLanguage(): void
    {
        $code = $_SERVER['HTTP_X_LANGUAGE'] ?? null;
        if (is_string($code) && $code !== '') {
            /** @var \LanguagesManager $languagesManager */
            $languagesManager = $this->getLanguagesManager();
            // setCurrentLanguageCode validates the code and ignores unknown ones.
            $languagesManager->setCurrentLanguageCode(strtolower($code));
            // publicStructureManager freezes its element path restriction to whichever
            // language was current when DI built it, which happens before this header is
            // applied. Without re-syncing it, element lookups keep searching inside the
            // previous language branch and elements of the requested language are not found.
            /** @var \structureManager $structureManager */
            $structureManager = $this->getService('publicStructureManager');
            $structureManager->setElementPathRestrictionId($languagesManager->getCurrentLanguageId());
        }
    }

    protected function logThrowable(string $location, Throwable $throwable): void
    {
        $this->logger->error(
            $location . ': ' . $throwable->getMessage() . "\n"
            . $throwable->getTraceAsString() . "\n"
            . 'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | '
            . 'Referer: ' . ($_SERVER['HTTP_REFERER'] ?? 'unknown') . ' | '
            . 'URL: ' . $this->getRequestUrl()
        );
    }
}
