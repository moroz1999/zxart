<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use Override;
use ZxArt\ZxScreen\ZxPictureParametersDto;
use ZxArt\ZxScreen\ZxPictureUrlHelper;

/**
 * Permanent redirect from the historical picture addresses
 * (`/zxscreen/border:0/mode:mix/pal:srgb/type:standard/zoom:1/id:572989`) to the
 * controller rendering them now. The parameters are the same ones, written as
 * path segments, so they arrive as ordinary request parameters and go straight
 * to the helper that builds every current picture address.
 */
class Zxscreen extends controllerApplication
{
    public $rendererName = 'fileReader';

    #[Override]
    public function initialize()
    {
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller)
    {
        $id = $this->readInt($controller->getParameter('id'));
        if ($id === null) {
            $this->renderer->fileNotFound();
            return;
        }

        $parameters = new ZxPictureParametersDto(
            type: $this->readString($controller->getParameter('type')),
            zoom: $this->readInt($controller->getParameter('zoom')),
            id: $id,
            border: $this->readInt($controller->getParameter('border')),
            rotation: $this->readInt($controller->getParameter('rotation')),
            mode: $this->readString($controller->getParameter('mode')),
            palette: $this->readString($controller->getParameter('pal')),
        );

        $controller->redirect(ZxPictureUrlHelper::getUrl($controller->baseURL, $parameters), '301');
    }

    /**
     * `controller::getParameter()` answers with the raw request value, or `false`
     * when the parameter is absent, so both readers start from `mixed`.
     */
    private function readString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function readInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int)$value : null;
    }
}
