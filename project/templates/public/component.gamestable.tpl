<div class='games_list_block'>
	<table class='games_list_table table_component'>
		<thead>
			<tr>
				<th>
					{translations name='label.table_game'}
				</th>
				<th>
					{translations name='label.table_company'}
				</th>
				<th>
					{translations name='label.table_year'}
				</th>
				<th>
					{translations name='label.table_wos'}
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$gamesList item=game name=gamesList}
				<tr class="">
					<td>
						<a class='' href='{$game->URL}'>{$game->title}</a>
					</td>
					<td>
						{$game->company}
					</td>
					<td>
						{$game->year}
					</td>
					<td>
						{if $game->wosURL}
							<a class='newwindow_link' href="{$game->wosURL}"><img loading="lazy" src="{$theme->getImageUrl("wos.png")}" /></a>
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>