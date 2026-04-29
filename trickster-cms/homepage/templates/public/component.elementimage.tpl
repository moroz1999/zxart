{stripdomspaces}
{if empty($src)}
	{$src = $theme->generateImageUrl($element->image, $element->originalName, $type)}
	{$srcset = $theme->generateImageSrcSet($element->image, $element->originalName, $type)}
{/if}
{if !isset($title)}
	{if $element->title}
		{$title = $element->title}
	{else}
		{$title = ''}
	{/if}
{/if}
{if empty($class)}
	{$class = ''}
{/if}
{if empty($lazy)}
	<img class="{$class}" src="{$src}" {if !empty($srcset)}srcset="{$srcset}"{/if} alt="{$title}"/>
{else}
	<img class="{$class} lazy_image" src="" data-lazysrc="{$src}" {if !empty($srcset)}data-lazysrcset="{$srcset}"{/if} alt="{$title}"/>
{/if}
{/stripdomspaces}