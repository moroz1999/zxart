<img src='{$element->getImageUrl()}' alt='{$element->title}'/><br>
<a href='{$element->getUrl()}'>{$element->title}</a> - {foreach from=$element->getAuthorsList() item=author name=authors}<a href="{$author->getUrl()}">{$author->title}</a>{if !$smarty.foreach.authors.last} &amp; {/if}{/foreach}<br>
{if $element->getPartyElement()}<a href='{$element->getPartyElement()->URL}'>{$element->getPartyElement()->title}</a>({if $element->partyplace > 0}{$element->partyplace}{/if})<br>{/if}
<br><br>