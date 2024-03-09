{if $element->getVotesHistory()}
	{translations name='label.voteshistory'}<br><br>
	{foreach from=$element->getVotesHistory() item=voteInfo name=votes}
		{if $voteInfo.userUrl}<a href="{$voteInfo.userUrl}">{/if}{$voteInfo.userName}{if $voteInfo.userUrl}</a>{/if} <b>{$voteInfo.value}</b> {$voteInfo.date}<br>
	{/foreach}
{/if}<br>