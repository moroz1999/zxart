{if $showPartyPlace}{$element->partyplace}{else}{$number}{/if}. <a href='{$element->getUrl()}'>{$element->title}</a>
{if $showAuthors} - {foreach from=$element->getAuthorsList() item=author name=authors}<a href='{$author->getUrl()}'>{$author->title}</a>{if !$smarty.foreach.authors.last}, {/if}{/foreach}{/if}
{$element->type}{if $showYear} {if $element->year != 0}{$element->year}{/if}{/if}
<br>
{if $partyElement = $element->getPartyElement()}<a href='{$partyElement->URL}'>{$partyElement->title}</a> {if $partyElement}{if $element->partyplace != 0}({$element->partyplace}) {/if}{/if}<br>{/if}
{if $element->getGameElement()}<a href='{$element->getGameElement()->URL}'>{$element->getGameElement()->title}</a><br>{/if}
<br>