{if !isset($showPartyPlace)}{assign var="showPartyPlace" value=false}{/if}
{if !isset($showAuthors)}{assign var="showAuthors" value=true}{/if}
{if !isset($showYear)}{assign var="showYear" value=true}{/if}
{if isset($pager)}
	<div class='music_list_top_controls'>
		{include file=$theme->template("pager.tpl") pager=$pager}
	</div>
{/if}
{if !isset($number)}{$number=1}{/if}

<div class='music_list_block'>
	<table class='music_list_table table_component'>
		<thead>
			<tr>
				<th class='music_list_number'>

				</th>
				<th class='music_list_player'>

				</th>
				<th class='music_list_title'>
					{translations name='zxmusic.table_title'}
				</th>
				{if $showAuthors}
				<th class='music_list_authors'>
					{translations name='zxmusic.table_authors'}
				</th>
				{/if}
				<th class='music_list_format'>
				</th>
				{if $showYear}
				<th class='music_list_year'>
					{translations name='zxmusic.table_year'}
				</th>
				{/if}
				<th class='music_list_votecontrols'>
					{translations name='zxmusic.table_votes'}
				</th>
				<th class='music_list_votesamount'>

				</th>
				<th class='music_list_commentsamount'>

				</th>
				<th class='music_list_plays'>

				</th>
				<th class='music_list_source'>
				</th>
				<th class='music_list_compo'>

				</th>
				<th class='music_list_download'>
					{translations name='zxmusic.table_download'}
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$musicList item=music name=musicList}
				{if $smarty.foreach.musicList.iteration is odd}
					{include file=$theme->template("zxMusic.table.tpl") element=$music odd=1 number=$number}
				{else}
					{include file=$theme->template("zxMusic.table.tpl") element=$music odd=0 number=$number}
				{/if}
				{$number=$number+1}
			{/foreach}
		</tbody>
	</table>
</div>
{if isset($pager)}
	<div class='music_list_bottom_controls'>
		{include file=$theme->template("pager.tpl") pager=$pager}
	</div>
{/if}
