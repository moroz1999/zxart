<?php
declare(strict_types=1);

interface RendererPluginAppendInterface
{
    public function appendResponseData($type, $value);
}