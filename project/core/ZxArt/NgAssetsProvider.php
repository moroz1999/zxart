<?php

namespace ZxArt;

class NgAssetsProvider
{
    private const MANIFEST_PATH = ROOT_PATH . 'htdocs/js/ng-zxart/manifest.json';
    private const WEB_BASE = '/js/ng-zxart/';
    private const DEV_SERVER_SCRIPTS = [
        'runtime.js',
        'styles.js',
        'main.js',
    ];
    private const DEV_SERVER_STYLES = [
        'styles.css',
    ];

    /** @var string[] */
    private array $scripts = [];

    /** @var string[] */
    private array $styles = [];

    public function __construct()
    {
        if ($devServerUrl = $this->getDevServerUrl()) {
            foreach (self::DEV_SERVER_SCRIPTS as $file) {
                $this->scripts[] = $devServerUrl . '/' . $file;
            }
            foreach (self::DEV_SERVER_STYLES as $file) {
                $this->styles[] = $devServerUrl . '/' . $file;
            }

            return;
        }

        if (!is_file(self::MANIFEST_PATH)) {
            return;
        }
        $data = json_decode(file_get_contents(self::MANIFEST_PATH), true);
        if (!is_array($data)) {
            return;
        }
        foreach ($data['scripts'] ?? [] as $file) {
            $this->scripts[] = self::WEB_BASE . $file;
        }
        foreach ($data['styles'] ?? [] as $file) {
            $this->styles[] = self::WEB_BASE . $file;
        }
    }

    private function getDevServerUrl(): ?string
    {
        $url = getenv('NG_DEV_SERVER_URL');
        if (!is_string($url) || trim($url) === '') {
            return null;
        }

        return rtrim(trim($url), '/');
    }

    /** @return string[] */
    public function getScriptUrls(): array
    {
        return $this->scripts;
    }

    /** @return string[] */
    public function getStyleUrls(): array
    {
        return $this->styles;
    }
}
