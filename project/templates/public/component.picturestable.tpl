{if isset($pager)}
	<div class='zxpictures_top_controls'>
		{include file=$theme->template("pager.tpl") pager=$pager}
	</div>
{/if}
{if !isset($number)}{$number=1}{/if}

<div class='pictures_list_block gallery_pictures' id="gallery_{$element->id}">
	<table class='pictures_list_table table_component'>
		<thead>
			<tr>
				<th class='pictures_list_number'>

				</th>
				<th class='pictures_list_image'>

				</th>
				<th class='pictures_list_title'>
					{translations name='label.table_title'}
				</th>
				<th class='pictures_list_authors'>
					{translations name='label.table_authors'}
				</th>
				<th class='pictures_list_year'>
					{translations name='label.table_year'}
				</th>
				<th class='pictures_list_votecontrols'>
					{translations name='label.table_votes'}
				</th>
				<th class='pictures_list_votesamount'>
				</th>
				<th class='pictures_list_commentsamount'>
				</th>
				<th class='pictures_list_views'>
				</th>

				<th class='pictures_list_source'>
				</th>
				<th class='pictures_list_partyplace'>
				</th>
				<th class='pictures_list_download'>
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$picturesList item=picture name=picturesList}
				{if $smarty.foreach.picturesList.iteration is odd}
					{include file=$theme->template("zxPicture.table.tpl") element=$picture odd=1 number=$number}
				{else}
					{include file=$theme->template("zxPicture.table.tpl") element=$picture odd=0 number=$number}
				{/if}
				{$number=$number+1}
			{/foreach}
		</tbody>
	</table>
</div>
{if isset($pager)}
	<div class='zxpictures_bottom_controls'>
		{include file=$theme->template("pager.tpl") pager=$pager}
	</div>
{/if}