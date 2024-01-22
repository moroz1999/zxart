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
				<td>{include file=$theme->template("component.username.tpl") userName=$voteInfo.userName userUrl=$voteInfo.userUrl userType=$voteInfo.userType}</td>
				<td>{$voteInfo.value}</td>
				<td>{$voteInfo.date}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/if}