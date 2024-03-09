{stripdomspaces}
{if empty($src)}
	{$src = $element->generateImageUrl($type)}
{/if}
{if !isset($title)}
	{if $element->title}
		{$title = $element->title}
	{else}
		{$title = ''}
	{/if}
{/if}
<img src="{$src}" alt="{$title}"/>{/stripdomspaces}