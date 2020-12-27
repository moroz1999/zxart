{$number}. <a class='' href='{$element->getUrl()}'>{$element->title}</a> {if $authorElement = $element->getAuthorElement()}
{foreach $element->getGroupsList() as $groupElement}<a href="{$groupElement->getUrl()}">{$groupElement->title}</a>{if !$groupElement@last}, {/if}{/foreach}<br>
	<a href="{$authorElement->getUrl()}">{$authorElement->title}</a> {if $authorElement->getCountryTitle()}{$authorElement->getCountryTitle()}, {/if}{if $authorElement->getCityTitle()}{$authorElement->getCityTitle()}{/if}<br><br>
{/if}