<?php

interface LdJsonProviderInterface
{
    public function getLdJsonScriptHtml();

    /** @return array<array-key, mixed> */
    public function getLdJsonScriptData();
}
