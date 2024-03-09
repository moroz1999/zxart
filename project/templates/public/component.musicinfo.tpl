{if $element->description}
	<div class="music_details_description">
		{$element->description}
	</div>
{/if}
<table class='music_details_info info_table'>
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.title'}:
		</td>
		<td class='info_table_value'>
			{$element->title}
		</td>
	</tr>
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.author'}:
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
	{if $element->type}
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.format'}:
		</td>
		<td class='info_table_value'>
			{if isset($musicDetailedSearchElement)}
				<a href="{$musicDetailedSearchElement->URL}format:{$element->type}/">{$element->type}</a>
			{else}
				{$element->type}
			{/if}
		</td>
	</tr>
	{/if}
	{if $element->formatGroup}
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.formatgroup'}:
		</td>
		<td class='info_table_value'>
			{assign "formatgroup" 'zxmusic.formatgroup_'|cat:$element->formatGroup}
			{if isset($musicDetailedSearchElement)}
				<a href="{$musicDetailedSearchElement->URL}formatGroup:{$element->formatGroup}/">{translations name=$formatgroup}</a>
			{else}
				{translations name=$formatgroup}
			{/if}
		</td>
	</tr>
	{/if}
	{if $element->hasChipChannelsType()}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.chiptype'}:
			</td>
			<td class='info_table_value'>
				{translations name="zxmusic.chiptype_{$element->getChipType()}"}
			</td>
		</tr>
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.channelstype'}:
			</td>
			<td class='info_table_value'>
				{translations name="zxmusic.channelstype_{$element->getChannelsType()}"}
			</td>
		</tr>
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.frequency'}:
			</td>
			<td class='info_table_value'>
				{translations name="zxmusic.frequency_{$element->getFrequency()}"}
			</td>
		</tr>
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.intFrequency'}:
			</td>
			<td class='info_table_value'>
				{translations name="zxmusic.intFrequency_{$element->getIntFrequency()|replace:".":""}"}
			</td>
		</tr>
	{/if}
	{if $element->getPartyElement()}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.party'}:
			</td>
			<td class='info_table_value'>
				{assign 'compoTitle' "compo_"|cat:$element->compo}
				<a href='{$element->getPartyElement()->URL}'>{$element->getPartyElement()->title}</a>
				({if !empty($element->partyplace)}{$element->partyplace}, {/if}{translations name="musiccompo.$compoTitle"})
			</td>
		</tr>
	{/if}
	{if $element->getReleaseElement()}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.release'}:
			</td>
			<td class='info_table_value'>
				<a href='{$element->getReleaseElement()->URL}'>{$element->getReleaseElement()->title}</a>
			</td>
		</tr>
	{/if}
	{if $element->year != '0'}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.year'}:
			</td>
			<td class='info_table_value'>
				<a href="{$musicDetailedSearchElement->URL}startYear:{$element->year}/endYear:{$element->year}/">{$element->year}</a>
			</td>
		</tr>
	{/if}
	{if $element->inspired != ''}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.inspired'}:
			</td>
			<td class='info_table_value'>
				{$element->inspired}
			</td>
		</tr>
	{/if}
	{if $element->time != ''}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.time'}:
			</td>
			<td class='info_table_value'>
				{$element->time}
			</td>
		</tr>
	{/if}
	{if $element->channels != ''}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.channels'}:
			</td>
			<td class='info_table_value'>
				{$element->channels}
			</td>
		</tr>
	{/if}
	{if $element->container != ''}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.container'}:
			</td>
			<td class='info_table_value'>
				{$element->container}
			</td>
		</tr>
	{/if}
	{if $element->program != ''}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.program'}:
			</td>
			<td class='info_table_value'>
				{$element->program}
			</td>
		</tr>
	{/if}
	{if $element->internalTitle != ''}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.internalTitle'}:
			</td>
			<td class='info_table_value'>
				{$element->internalTitle}
			</td>
		</tr>
	{/if}
	{if $element->internalAuthor != ''}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.internalAuthor'}:
			</td>
			<td class='info_table_value'>
				{$element->internalAuthor}
			</td>
		</tr>
	{/if}
	{if $element->fileName}
		<tr>
			<td class='info_table_label'>
				{translations name='zxitem.originalFileName'}:
			</td>
			<td class='info_table_value'>
				{$element->fileName|urldecode}
			</td>
		</tr>
	{/if}
	{if $element->getFileName('original')}
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.originalfile'}:
		</td>
		<td class='info_table_value'>
			<a rel="nofollow" class='music_details_download' href="{$controller->baseURL}file/id:{$element->file}/filename:{$element->getFileName('original')}"><img loading="lazy"  class="music_details_original" src="{$theme->getImageUrl("music.png")}" alt="{translations name='zxmusic.originalfile'} {$element->getFileName('original', false)}" />{$element->getFileName('original', false, false)}</a>
		</td>
	</tr>
	{/if}
	{if $element->getFileName('tracker')}
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.trackerfile'}:
		</td>
		<td class='info_table_value'>
			<a rel="nofollow" class='music_details_download' href="{$controller->baseURL}file/id:{$element->trackerFile}/filename:{$element->getFileName('tracker')}"><img loading="lazy" class="music_details_tracker" src="{$theme->getImageUrl("tracker.png")}" alt="{translations name='zxmusic.trackerfile'} {$element->getFileName('tracker', false)}" />{$element->getFileName('tracker', false, false)}</a>
		</td>
	</tr>
	{/if}
	{if $element->getMp3FilePath()}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.mp3file'}:
			</td>
			<td class='info_table_value'>
				<a rel="nofollow" class='music_details_download' href="{$element->getMp3FilePath()}"><img loading="lazy" class="music_details_mp3" src="{$theme->getImageUrl("mp3.png")}" alt="{translations name='zxmusic.mp3file'} {$element->getFileName('mp3', false)}" /> {$element->getMp3FilePath()}</a>
			</td>
		</tr>
	{/if}
	{if $element->getTagsList()}
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.tags'}:
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
			{translations name='zxmusic.votes'}:
		</td>
		<td class='info_table_value'>
			{include file=$theme->template("component.votecontrols.tpl") element=$element}
			{include file=$theme->template("component.playlist.tpl") element=$element}
			{if !$element->isVotingDenied() && $element->getVotePercent()}
				<div>{$element->votes}</div>
			{/if}
		</td>
	</tr>
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.plays'}:
		</td>
		<td class='info_table_value'>
			{$element->plays}
		</td>
	</tr>

	{if $element->converterVersion}
		<tr>
			<td class='info_table_label'>
				{translations name='zxmusic.converterVersion'}:
			</td>
			<td class='info_table_value'>
				<a href="https://zxtune.bitbucket.io/" target="_blank">ZXTune r{$element->converterVersion}</a>
			</td>
		</tr>
	{/if}
	{assign var="userElement" value=$element->getUser()}
	{if $userElement}
	<tr>
		<td class='info_table_label'>
			{translations name='zxmusic.addedby'}:
		</td>
		<td class='info_table_value'>
			{$userElement->userName}, {$element->dateCreated}
		</td>
	</tr>
	{/if}
</table>
