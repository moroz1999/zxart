<!DOCTYPE html>
<html lang="{$currentLanguage->iso6391|escape:'html'}" class="{$currentThemeClass|default:'dark-mode'|escape:'html'}">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="format-detection" content="telephone=no" />
	{if !empty($themeColor)}<meta name="theme-color" content="{$themeColor|escape:'html'}" />{/if}
	<title>{$pageMetadata->title|escape:'html'}</title>
	{if $pageMetadata->description !== ''}<meta name="description" content="{$pageMetadata->description|escape:'html'}" />{/if}
	{if $pageMetadata->noIndex}<meta name="robots" content="noindex" />{/if}
	{foreach $pageMetadata->openGraph as $key=>$value}<meta property="og:{$key|escape:'html'}" content="{$value|escape:'html'}" />{/foreach}
	{foreach $pageMetadata->twitter as $key=>$value}<meta property="twitter:{$key|escape:'html'}" content="{$value|escape:'html'}" />{/foreach}
	{foreach $pageMetadata->languageLinks as $code=>$url}<link rel="alternate" hreflang="{$code|escape:'html'}" href="{$url|escape:'html'}" />{/foreach}
	{if $structuredDataJson !== null}<script type="application/ld+json">{$structuredDataJson}</script>{/if}
	{* Roboto is variable: one subset file covers the 300/400/500 faces, so a single preload per subset covers all page text.
	   Content mixes latin and cyrillic regardless of the interface language, so both subsets are always preloaded. *}
	<link rel="preload" as="font" type="font/woff2" href="{$controller->baseURL}fonts/roboto/roboto-latin.woff2" crossorigin />
	<link rel="preload" as="font" type="font/woff2" href="{$controller->baseURL}fonts/roboto/roboto-cyrillic.woff2" crossorigin />
	<link rel="stylesheet" href="{$controller->baseURL}fonts/roboto/fonts.css" />
	<link rel="shortcut icon" href="{$controller->baseURL}favicon.ico" />
	<link rel="alternate" type="application/rss+xml" href="{$controller->baseURL}rss/{$currentLanguage->iso6393}/" title="RSS" />
	{foreach $ngStyleUrls as $url}<link rel="stylesheet" href="{$url}" crossorigin="anonymous">{/foreach}
	{foreach $ngScriptUrls as $url}<script src="{$url}" type="module"></script>{/foreach}
</head>
<body>
<app-root></app-root>
</body>
</html>
