{if isset($pager)}
	<div class='authors_list_controls'>
		{include file=$theme->template("pager.tpl") pager=$pager}
	</div>
{/if}
{if !isset($number)}{$number=1}{/if}

<div class='authors_list_block'>
	<table class='authors_list_table table_component'>
		<thead>
			<tr>
				<th class="author_table_number">
					
				</th>
				<th class="author_table_title">
					{translations name='label.table_nick'}
				</th>
				<th class="author_table_groups">
					{translations name='label.table_group'}
				</th>
				<th class="author_table_realname">
					{translations name='label.table_realname'}
				</th>
				<th class="author_table_country">
					{translations name='label.table_country'}
				</th>
				<th class="author_table_city">
					{translations name='label.table_city'}
				</th>
				<th class="author_table_musicrating">
					{translations name='label.table_musicrating'}
				</th>
				<th class="author_table_graphicsrating">
					{translations name='label.table_graphicsrating'}
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$authorsList item=author name=authorsList}
				{$odd = $smarty.foreach.authorsList.iteration is odd}
				{include file=$theme->template($author->getTemplate('table')) element=$author odd=$odd}
				{$number=$number+1}
			{/foreach}
		</tbody>
	</table>
</div>
{if isset($pager)}
	<div class='authors_list_controls'>
		{include file=$theme->template("pager.tpl") pager=$pager}
	</div>
{/if}