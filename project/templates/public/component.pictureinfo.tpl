{if $element->description}
<p>
	{$element->description}
</p>
{/if}

<table class='picture_details_info info_table'>
	<tr>
		<td class='info_table_label'>
			{translations name='field.title'}:
		</td>
		<td class='info_table_value'>
			{$element->title}
		</td>
	</tr>
	<tr>
		<td class='info_table_label'>
			{translations name='field.author'}:
		</td>
		<td class='info_table_value'>
			{foreach from=$element->getAuthorsList() item=author name=authors}
				<a href='{$author->getUrl()}'>{$author->title}</a>
				{if $author->structureType == 'authorAlias'}
					{if $realAuthor = $author->getAuthorElement()}
						(<a href='{$realAuthor->getUrl()}'>{$realAuthor->title}</a>)
					{/if}
				{/if}
				{if !$smarty.foreach.authors.last}, {/if}
			{/foreach}
		</td>
	</tr>
	{if $originalAuthors = $element->getOriginalAuthorsList()}
	<tr>
		<td class='info_table_label'>
			{translations name='zxPicture.original_author'}:
		</td>
		<td class='info_table_value'>
			{foreach from=$originalAuthors item=author name=authors}
				<a href='{$author->getUrl()}'>{$author->title}</a>
				{if $author->structureType == 'authorAlias'}
					{if $realAuthor = $author->getAuthorElement()}
						(<a href='{$realAuthor->getUrl()}'>{$realAuthor->title}</a>)
					{/if}
				{/if}
				{if !$smarty.foreach.authors.last}, {/if}
			{/foreach}
		</td>
	</tr>
	{/if}
	<tr>
		<td class='info_table_label'>
			{translations name='field.format'}:
		</td>
		<td class='info_table_value'>
			{if isset($picturesDetailedSearchElement)}
				<a href="{$picturesDetailedSearchElement->URL}pictureType:{$element->type}/">{translations name=$element->getZxPictureTypeTranslation($element->type)}</a>
			{else}
				{translations name=$element->getZxPictureTypeTranslation($element->type)}
			{/if}
		</td>
	</tr>
	<tr>
		<td class='info_table_label'>
			{translations name='zxpicture.palette'}:
		</td>
		<td class='info_table_value'>
			{translations name="zxpicture.palette_{$element->getPalette()}"}
		</td>
	</tr>
	{if $element->getPartyElement()}
		<tr>
			<td class='info_table_label'>
				{translations name='field.party'}:
			</td>
			<td class='info_table_value'>
				{assign 'compoTitle' "compo_"|cat:$element->compo}
				<a href='{$element->getPartyElement()->URL}'>{$element->getPartyElement()->title}</a>
				({if !empty($element->partyplace)}{$element->partyplace}, {/if}{translations name="zxPicture.$compoTitle"})
			</td>
		</tr>
	{/if}
	{if $element->getReleaseElement()}
		<tr>
			<td class='info_table_label'>
				{translations name='zxpicture.release'}:
			</td>
			<td class='info_table_value'>
				<a href='{$element->getReleaseElement()->URL}'>{$element->getReleaseElement()->title}</a>
			</td>
		</tr>
	{/if}
	{if $element->year != '0'}
		<tr>
			<td class='info_table_label'>
				{translations name='field.year'}:
			</td>
			<td class='info_table_value'>
				<a href="{$picturesDetailedSearchElement->URL}startYear:{$element->year}/endYear:{$element->year}/">{$element->year}</a>
			</td>
		</tr>
	{/if}
	{if $element->originalName}
		<tr>
			<td class='info_table_label'>
				{translations name='zxitem.originalFileName'}:
			</td>
			<td class='info_table_value'>
				{$element->originalName|urldecode}
			</td>
		</tr>
	{/if}
	{if $element->getFileName('original', false)}
	<tr>
		<td class='info_table_label'>

		</td>
		<td class='info_table_value'>
			<a rel="nofollow" class='picture_details_download' href="{$controller->baseURL}file/id:{$element->id}/filename:{$element->getFileName()}"><img loading="lazy" src="{$theme->getImageUrl("disk.png")}" alt="{translations name='label.download'} {$element->getFileName('original', false)}" />{translations name='field.originalfile'}</a>
		</td>
	</tr>
	<tr>
		<td class='info_table_label'>

		</td>
		<td class='info_table_value'>
			<a rel="nofollow" class='picture_details_download' href="{$element->getDownloadUrl(1)}"><img loading="lazy" src="{$theme->getImageUrl("disk.png")}" alt="{translations name='label.download'}" />{translations name='zxpicture.download_pc'}</a>
			<a rel="nofollow" class='picture_details_download' href="{$element->getDownloadUrl(2)}">2X</a>
			<a rel="nofollow" class='picture_details_download' href="{$element->getDownloadUrl(3)}">3X</a>
			<a rel="nofollow" class='picture_details_download' href="{$element->getDownloadUrl(4)}">4X</a>
		</td>
	</tr>
	<tr>
		<td class='info_table_label'>

		</td>
		<td class='info_table_value'>
			<a rel="nofollow" class='picture_details_download' href="{$controller->baseURL}print/id:{$element->image}/fileName:{$element->getFileName('image', true, true)}/"><img loading="lazy" src="{$theme->getImageUrl("disk.png")}" alt="{translations name='label.download'}" />{translations name='zxpicture.download_print'}</a>
		</td>
	</tr>
	{/if}
	{if $element->exeFile != ''}
	<tr>
		<td class='info_table_label'>
			{translations name='field.exefile'}:
		</td>
		<td class='info_table_value'>
			<a rel="nofollow" class='picture_details_download' href="{$controller->baseURL}file/id:{$element->exeFile}/filename:{$element->getFileName('exe')}"><img loading="lazy" src="{$theme->getImageUrl("disk.png")}" alt="{translations name='label.download'} {$element->getFileName('original', false)}" />{$element->getFileName('exe', false, false)}</a>
		</td>
	</tr>
	{/if}

	{if $element->getTagsList()}
	<tr>
		<td class='info_table_label'>
			{translations name='field.tags'}:
		</td>
		<td class='info_table_value'>
			{foreach from=$element->getTagsList() item=tag name=tags}
				<a href='{$tag->URL}'>{$tag->title}</a>{if !$smarty.foreach.tags.last}, {/if}
			{/foreach}
		</td>
	</tr>
	{/if}
	<tr>
		<td class='info_table_label'>
			{translations name='field.votes'}:
		</td>
		<td class='info_table_value'>
			<zx-item-controls element-id="{$element->id}" type="zxPicture" votes="{$element->votes}" votes-amount="{$element->votesAmount}" user-vote="{$element->getUserVote()}" deny-voting="{if $element->isVotingDenied()}true{else}false{/if}"></zx-item-controls>
			{if !$element->isVotingDenied() && $element->getVotePercent()}
			<div>{$element->votes}</div>
			{/if}
		</td>
	</tr>
	<tr>
		<td class='info_table_label'>
			{translations name='picture.views'}:
		</td>
		<td class='info_table_value'>
			{$element->views}
		</td>
	</tr>
	{if $element->artCityId}
	<tr>
		<td class='info_table_label'>
			{translations name='picture.artcity'}:
		</td>
		<td class='info_table_value'>
			<a href="https://artcity.bitfellas.org/index.php?a=show&id={$element->artCityId}">http://artcity.bitfellas.org/index.php?a=show&id={$element->artCityId}</a>
		</td>
	</tr>
	{/if}
	{assign var="userElement" value=$element->getUserElement()}
	{if $userElement}
	<tr>
		<td class='info_table_label'>
			{translations name='picture.addedby'}:
		</td>
		<td class='info_table_value'>
			{$userElement->userName}, {$element->dateCreated}
		</td>
	</tr>
	{/if}
</table>