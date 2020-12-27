{if $links = $element->getLinksInfo()}
	{foreach $links as $linkInfo}
		<a target="_blank" class="zxprod_link" href='{$linkInfo['url']}'><img class="zxprod_link_image" src="{$theme->getImageUrl($linkInfo.image)}"></a>
	{/foreach}
{/if}