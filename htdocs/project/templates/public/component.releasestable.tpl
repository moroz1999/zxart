{if isset($pager)}
	<div class='releases_list_top_controls'>
		{include file=$theme->template("pager.tpl") pager=$pager}
	</div>
{/if}
{if !isset($number)}{$number=1}{/if}

<div class='releases_list_block gallery_releases'>
	<table class='releases_list_table table_component'>
		<thead>
			<tr>
				<th class='zxrelease_table_number'>

				</th>
				<th class='zxrelease_table_title'>
					{translations name='label.table_title'}
				</th>
				<th class='zxrelease_table_year'>
					{translations name='label.table_year'}
				</th>
				<th class='zxrelease_table_play'>
				</th>
				<th class='zxrelease_table_source'>
				</th>
				<th class='zxrelease_table_partyplace'>
				</th>
				<th class='zxrelease_table_format'>
				</th>
				<th class='zxrelease_table_language'>
				</th>
				<th class='zxrelease_table_version'>
				</th>
				<th class='zxrelease_table_releasetype'>
				</th>
				<th class='zxrelease_table_releaseby'>
					{translations name='zxrelease.releaseby'}
				</th>
				<th class='zxrelease_table_hardware'>
					{translations name='zxrelease.hardware'}
				</th>
				<th class='zxrelease_table_links'>
				</th>
				<th class='zxrelease_table_download'>
				</th>
				<th class='zxrelease_table_downloaded'>
					{translations name='zxrelease.downloads'}
				</th>
				<th class='zxrelease_table_plays'>
					{translations name='zxrelease.plays'}
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$releasesList item=release name=releasesList}
				{if $smarty.foreach.releasesList.iteration is odd}
					{include file=$theme->template("zxRelease.table.tpl") element=$release odd=1 number=$number}
				{else}
					{include file=$theme->template("zxRelease.table.tpl") element=$release odd=0 number=$number}
				{/if}
				{$number=$number+1}
			{/foreach}
		</tbody>
	</table>
</div>
{if isset($pager)}
	<div class='releases_list_bottom_controls'>
		{include file=$theme->template("pager.tpl") pager=$pager}
	</div>
{/if}