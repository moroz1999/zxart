<?php

namespace ZxArt;

class NgAssetsProvider
{
    private const MANIFEST_PATH = ROOT_PATH . 'htdocs/js/ng-zxart/manifest.json';
    private const WEB_BASE = '/js/ng-zxart/';

    /** @var string[] */
    private array $scripts = [];

    /** @var string[] */
    private array $styles = [];

    public function __construct()
    {
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
