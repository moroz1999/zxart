<!DOCTYPE html>
<html lang="{$currentLanguage->iso6391|escape:'html'}" class="{$currentThemeClass|default:'dark-mode'|escape:'html'}">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="format-detection" content="telephone=no" />
	{if !empty($themeColor)}<meta name="theme-color" content="{$themeColor|escape:'html'}" />{/if}
	<title>{$pageMetadata->title|/escape:'html'}</title>
	{if $pageMetadata->description !== ''}<meta name="description" content="{$pageMetadata->description|escape:'html'}" />{/if}
	{if $pageMetadata->noIndex}<meta name="robots" content="noindex" />{/if}
	{foreach $pageMetadata->openGraph as $key=>$value}<meta property="og:{$key|escape:'html'}" content="{$value|escape:'html'}" />{/foreach}
	{foreach $pageMetadata->twitter as $key=>$value}<meta property="twitter:{$key|escape:'html'}" content="{$value|escape:'html'}" />{/foreach}
	{foreach $pageMetadata->languageLinks as $code=>$url}<link rel="alternate" hreflang="{$code|escape:'html'}" href="{$url|escape:'html'}" />{/foreach}
	{if $structuredDataJson !== null}<script type="application/ld+json">{$structuredDataJson}</script>{/if}
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet" />
	<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap" rel="stylesheet" />
	<link rel="shortcut icon" href="{$controller->baseURL}favicon.ico" />
	<link rel="alternate" type="application/rss+xml" href="{$controller->baseURL}rss/{$currentLanguage->iso6393}/" title="RSS" />
	{foreach $ngStyleUrls as $url}<link rel="stylesheet" href="{$url}" crossorigin="anonymous">{/foreach}
	{foreach $ngScriptUrls as $url}<script src="{$url}" type="module"></script>{/foreach}
</head>
<body>
<app-root></app-root>
</body>
</html>
