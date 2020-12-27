{if $element->getVotesHistory()}
	<h2 class='votes_list_title'>{translations name='label.voteshistory'}</h2>
	<table class="votes_list_table table_component">
		<thead>
		<tr>
			<th>
				{translations name='label.table_nick'}
			</th>
			<th>
				{translations name='label.table_vote'}
			</th>
			<th>
				{translations name='label.table_date'}
			</th>
		</tr>
		</thead>
		<tbody>
		{foreach from=$element->getVotesHistory() item=voteInfo name=votes}
			<tr class="">
				<td>{if $voteInfo.userUrl}<a href="{$voteInfo.userUrl}">{/if}{$voteInfo.userName}{if $voteInfo.userUrl}</a>{/if}</td>
				<td>{$voteInfo.value}</td>
				<td>{$voteInfo.date}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/if}