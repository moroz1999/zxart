{if $links = $element->getLinksInfo()}
	{foreach $links as $linkInfo}
		<a target="_blank" class="zxprod_link" href='{$linkInfo['url']}'><img loading="lazy" class="zxprod_link_image" src="{$theme->getImageUrl($linkInfo.image)}" alt="{$linkInfo.name}"></a>
	{/foreach}
{/if}