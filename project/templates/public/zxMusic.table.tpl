<tr class="">
	<td class='music_list_number'>
		{if $showPartyPlace}{$element->partyplace}{else}{$number}{/if}
	</td>
	<td class='music_list_player'>
		{if $element->isPlayable()}
			<div class="music_controls_short elementid_{$element->id}"></div>
		{/if}
	</td>
	<td class='music_list_title'>
		<a class='music_list_title_link' href='{$element->getUrl()}'>{$element->getTitle()} {if $element->isRealtime()}{assign 'compoTitle' "compo_"|cat:$element->compo}<img loading="lazy" src="{$theme->getImageUrl("clock.png")}" title="{translations name="musiccompo.$compoTitle"}" />{/if}</a>
	</td>
	{if $showAuthors}
	<td class='music_list_authors'>
		{foreach from=$element->getAuthorsList() item=author name=authors}
			<a href='{$author->getUrl()}'>{$author->title}</a>{if !$smarty.foreach.authors.last}, {/if}
		{/foreach}
	</td>
	{/if}
	<td class='music_list_format'>
		{$element->type}
	</td>
	{if $showYear}
	<td class='music_list_year'>
		{if $element->year != 0}{$element->year}{/if}
	</td>
	{/if}
	<td class='music_list_votecontrols'>
		{include file=$theme->template("component.votecontrols.tpl") element=$element}
		{include file=$theme->template("component.playlist.tpl") element=$element}
	</td>
	<td class='music_list_votesamount'>
		{if $element->votesAmount > 0}{$element->votesAmount}{/if}
	</td>
	<td class='music_list_commentsamount'>
		{if $element->commentsAmount > 0}
			{$element->commentsAmount}
		{/if}
	</td>
	<td class='music_list_plays'>
		{if $element->plays > 0}
			{$element->plays}
		{/if}
	</td>
	<td class='music_list_source'>
		{$partyElement = $element->getPartyElement()}
		{if $partyElement}
			<a href='{$partyElement->URL}'>{if $partyElement->abbreviation}{$partyElement->abbreviation}{else}{$partyElement->title}{/if}</a>
		{/if}
		{if $element->getReleaseElement()}
			<a href='{$element->getReleaseElement()->URL}'>{$element->getReleaseElement()->title}</a>
		{/if}
	</td>
	<td class='music_list_compo'>
		{if $partyElement}
			{if $element->partyplace != 0}{$element->partyplace} {/if}
			{if $element->partyplace=='1'}<img loading="lazy" src="{$theme->getImageUrl("gold_cup.png")}" alt='{translations name='label.firstplace'}'/>{/if}
			{if $element->partyplace=='2'}<img loading="lazy" src="{$theme->getImageUrl("silver_cup.png")}" alt='{translations name='label.secondplace'}'/>{/if}
			{if $element->partyplace=='3'}<img loading="lazy" src="{$theme->getImageUrl("bronze_cup.png")}" alt='{translations name='label.thirdplace'}'/>{/if}
		{/if}
	</td>
	<td class='music_list_download'>
		{if $element->getFileName('original')}<a rel="nofollow" href="{$controller->baseURL}file/id:{$element->file}/filename:{$element->getFileName('original')}"><img loading="lazy" class="music_list_original" src="{$theme->getImageUrl("music.svg")}" alt="{translations name='label.download'} {$element->getFileName('original', false)}" /> </a>{/if}
		{if $element->getFileName('tracker')}<a rel="nofollow" href="{$controller->baseURL}file/id:{$element->trackerFile}/filename:{$element->getFileName('tracker')}"><img loading="lazy" class="music_list_tracker" src="{$theme->getImageUrl("tracker.svg")}" alt="{translations name='label.download'} {$element->getFileName('tracker', false)}" /> </a>{/if}
		{if $element->getMp3FilePath()}<a rel="nofollow" href="{$element->getMp3FilePath()}"><img loading="lazy" class="music_list_mp3" src="{$theme->getImageUrl("mp3.svg")}" alt="{translations name='label.download'} {$element->getFileName('mp3', false)}" /> </a>{/if}
	</td>
</tr>
<script>
	{if $element->isPlayable()}
		if (!window.musicList) window.musicList = [];
		window.musicList.push({$element->getJsonInfo()});
	{/if}
</script>