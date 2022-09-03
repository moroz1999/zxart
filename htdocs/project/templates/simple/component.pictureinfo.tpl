{if $element->description}{$element->description}<br>{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>{/if}
	<b>{translations name='field.title'}:</b> {$element->title}	<br>
	<b>{translations name='field.author'}:</b> {foreach from=$element->getAuthorsList() item=author name=authors}
		<a href='{$author->getUrl()}'>{$author->title}</a>
		{if $author->structureType == 'authorAlias'}
			{if $realAuthor = $author->getAuthorElement()}
				(<a href='{$realAuthor->getUrl()}'>{$realAuthor->title}</a>)
			{/if}
		{/if}
		{if !$smarty.foreach.authors.last}, {/if}
	{/foreach}<br>
	<b>
		{translations name='field.format'}:
	</b>
	{if $element->type === 'chr$'}
		{assign "formatname" 'field.format_chr'}
	{else}
		{assign "formatname" 'field.format_'|cat:$element->type}
	{/if}
	{if isset($picturesDetailedSearchElement)}
		<a href="{$picturesDetailedSearchElement->URL}pictureType:{$element->type}/">{translations name=$formatname}</a>
	{else}
		{translations name=$formatname}
	{/if}<br>
{if $element->getPartyElement()}<b>{translations name='field.party'}:</b>
	{assign 'compoTitle' "compo_"|cat:$element->compo}<a href='{$element->getPartyElement()->URL}'>{$element->getPartyElement()->title}</a> ({if !empty($element->partyplace)}{$element->partyplace}, {/if}{translations name="zxPicture.$compoTitle"})<br>
{/if}
{if $element->getReleaseElement()}
	<b>{translations name='zxpicture.release'}:</b><a href='{$element->getReleaseElement()->URL}'>{$element->getReleaseElement()->title}</a><br>
{/if}
{if $element->year != '0'}
	<b>{translations name='field.year'}:</b> <a href="{$picturesDetailedSearchElement->URL}startYear:{$element->year}/endYear:{$element->year}/">{$element->year}</a><br>
{/if}
{if $element->getFileName('original', false)}
	<a rel="nofollow" class='picture_details_download' href="{$controller->baseURL}file/id:{$element->id}/filename:{$element->getFileName()}">{translations name='label.download'} {$element->getFileName('original', false)}</a><br>
{/if}
{if $element->getTagsList()}
	<b>{translations name='field.tags'}: </b>{foreach from=$element->getTagsList() item=tag name=tags}<a href='{$tag->URL}'>{$tag->title}</a>{if !$smarty.foreach.tags.last}, {/if}
{/foreach}
<br>{/if}
{if $element->md5}<b>{translations name='picture.md5'}:</b> {$element->md5}<br>{/if}
{if $userElement = $element->getUser()}
	<b>{translations name='picture.addedby'}:</b> {$userElement->userName}, {$element->dateCreated}<br>
{/if}
