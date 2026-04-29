{if $element->getVoteCount()}
<div class="tabs_content_item">

	<h1 class="poll_title">{$element->title}</h1>
	<h2 class="poll_description">{$element->description}</h2>
	{foreach from=$element->getQuestionsList() item=question name=questions}
		<table class="content_list poll_question_results">
			<thead>
				<tr>
					<th colspan="3">
						{$question->questionText}
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$question->getAnswersList() item=answer name=answers}
				<tr class="content_list_item elementid_{$contentItem->id}">
					<td class="poll_answer_column">
						{$answer->answerText}
					</td>


					{if $element->getAnswerResults($answer->id)}
						<td class="poll_result_bar_column">
							<div class="poll_result_bar">
								<div style="width: {$element->getAnswerResults($answer->id)}%;"></div>
							</div>
						</td>
						<td class="poll_result_percentage">{$element->getAnswerResults($answer->id)}%</td>
					{else}
						<td class="poll_result_bar_column">
							<div class="poll_result_bar"></div>
						</td>
						<td class="poll_result_percentage">0%</td>
					{/if}

				</tr>
				{/foreach}
			</tbody>
		</table>
	{/foreach}
		<div class="content_list_bottom">
			{if $element->getVoteCount()>0}
				<h2 class="poll_description voters_count">{translations name='poll.voters'}: {$element->getVoteCount()}</h2>
			{/if}
		</div>
</div>
{/if}