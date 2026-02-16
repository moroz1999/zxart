<tr class="">
	<td class='pictures_list_number'>
		{$number}
	</td>
	<td class='pictures_list_image'>
		<a href="{$picture->getUrl()}" class="pictures_list_image_link" onclick="return false;">
			<img loading="lazy" id='image_{$picture->id}' class='zxgallery_item{if $currentMode.mode != 'mix' && $element->isFlickering()} flicker_image{/if}' src='{$element->getImageUrl(1, false,false)}' alt='{$picture->title}'/>
		</a>
	</td>
	<td class='pictures_list_title'>
		<a class='' href='{$picture->getUrl()}'>{$picture->title} {if $picture->isRealtime()}{assign 'compoTitle' "compo_"|cat:$picture->compo}<img loading="lazy" src="{$theme->getImageUrl("clock.png")}" title="{translations name="zxPicture.$compoTitle"}" />{/if}</a>
	</td>
	<td class='pictures_list_authors'>
		{foreach from=$picture->getAuthorsList() item=author name=authors}
			<a href='{$author->getUrl()}'>{$author->title}</a>{if !$smarty.foreach.authors.last}, {/if}
		{/foreach}
	</td>
	<td class='pictures_list_year'>
		{if $picture->year != 0}{$picture->year}{/if}
	</td>
	<td class='pictures_list_votecontrols'>
		<zx-vote element-id="{$picture->id}" type="zxPicture" votes="{$picture->votes}" user-vote="{$picture->getUserVote()}" deny-voting="{if $picture->isVotingDenied()}true{else}false{/if}"></zx-vote>
		{include file=$theme->template("component.playlist.tpl") element=$element}
	</td>
	<td class='pictures_list_votesamount'>
		{if $picture->votesAmount > 0}{$picture->votesAmount}{/if}
	</td>
	<td class='pictures_list_commentsamount'>
		{if $picture->commentsAmount > 0}
			{$picture->commentsAmount}
		{/if}
	</td>
	<td class='pictures_list_views'>
		{if $picture->views > 0}
			{$picture->views}
		{/if}
	</td>
	<td class='pictures_list_source'>
		{$partyElement = $picture->getPartyElement()}
		{if $partyElement}
			<a href='{$partyElement->URL}'>{if $partyElement->abbreviation}{$partyElement->abbreviation}{else}{$partyElement->title}{/if}</a>
		{/if}
		{if $picture->getReleaseElement()}
			<a href='{$picture->getReleaseElement()->URL}'>{$picture->getReleaseElement()->title}</a>
		{/if}
	</td>
	<td class='pictures_list_partyplace'>
		{if $partyElement}
			{if $picture->partyplace!='0'}{$picture->partyplace}{/if}
			{if $picture->partyplace=='1'}<img loading="lazy" src="{$theme->getImageUrl("gold_cup.png")}" alt='{translations name='label.firstplace'}'/>{/if}
			{if $picture->partyplace=='2'}<img loading="lazy" src="{$theme->getImageUrl("silver_cup.png")}" alt='{translations name='label.secondplace'}'/>{/if}
			{if $picture->partyplace=='3'}<img loading="lazy" src="{$theme->getImageUrl("bronze_cup.png")}" alt='{translations name='label.thirdplace'}'/>{/if}
		{/if}
	</td>
	<td class='pictures_list_download'>
		<a rel="nofollow" href="{$controller->baseURL}file/id:{$picture->id}/filename:{$picture->getFileName()}"><img loading="lazy" src="{$theme->getImageUrl("disk.png")}" alt="{translations name='label.download'} {$picture->getFileName('original', false)}" /></a>
	</td>
</tr>
<script>
	if (!window.picturesList) window.picturesList = [];
	window.picturesList.push({$element->getJsonInfo()});
</script>
<script type='text/javascript'>
	if (!window.galleryPictures) window.galleryPictures = [];
	window.galleryPictures.push({$element->id});

	if (!window.imageInfoIndex) window.imageInfoIndex = {ldelim}{rdelim};
	window.imageInfoIndex['{$element->id}'] = {ldelim}
		'smallImage': "{$element->getImageUrl(1)}",
		'largeImage': "{$element->getImageUrl(2)}",
		'detailsURL': '{$element->URL}',
		'title': "{$element->title|escape:'javascript'}",
		'id': '{$element->id}',
		'flickering': '{$element->isFlickering() && ($currentMode.mode!='mix')}'
	{rdelim};
</script>