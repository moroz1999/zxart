<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Import;

use Override;
use ProdsDownloader;

/**
 * Serves import downloads from a local folder instead of the network: the file
 * a URL names is the fixture file of the same base name. A URL without a
 * fixture file fails as an unreachable one does.
 */
final class FixtureProdsDownloader extends ProdsDownloader
{
    private string $fixturesPath = '';

    public function setFixturesPath(string $fixturesPath): void
    {
        $this->fixturesPath = $fixturesPath;
    }

    #[Override]
    public function downloadUrl(string $url, string $destination): bool
    {
        $fixture = $this->fixturesPath . urldecode(basename((string)parse_url($url, PHP_URL_PATH)));
        if (!is_file($fixture)) {
            return false;
        }
        return copy($fixture, $destination);
    }
}
