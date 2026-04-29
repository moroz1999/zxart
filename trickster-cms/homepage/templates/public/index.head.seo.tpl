<title>{if !empty($currentMetaTitle)}{$currentMetaTitle}{/if}</title>
{if !empty($currentMetaDescription)}<meta name="description" content="{$currentMetaDescription}" />{/if}
{if !empty($currentNoIndexing)}<meta name="robots" content="noindex" />{/if}
{if !empty($currentCanonicalUrl)}<link rel="canonical" href="{$currentCanonicalUrl}"/>{/if}