{if $links = $element->getLinksInfo()}
	<b>{translations name='links.links'}: </b>{foreach $links as $linkInfo}<a href='{$linkInfo['url']}'>{$linkInfo.name}</a>{/foreach}<br>
{/if}