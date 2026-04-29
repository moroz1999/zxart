{assign var='formNames' value=$element->getFormNames()}

{capture assign="moduleContent"}
	{if $element->title}
		{capture assign="moduleTitle"}
			{$element->title}
		{/capture}
	{/if}
	{if $element->description}
		<div class="poll_results_description">{$element->description}</div>
	{/if}

	{foreach from=$element->getQuestionsList() item=question name=questions}

		<div class="poll_results_question_text">{$question->questionText}</div>

		{foreach from=$question->getAnswersList() item=answer name=answers}
			<div class="poll_answer_results">
				{if $element->getAnswerResults($answer->id)}
					<div class="poll_results_answertext">{$answer->answerText}
						<span>({$element->getAnswerResults($answer->id)}%)</span>
					</div>
					<div class="poll_result_bar">
						<div style="width: {$element->getAnswerResults($answer->id)}%;"></div>
					</div>
				{else}
					<div class="poll_results_answertext">{$answer->answerText}
						<span>(0%)</span>
					</div>
					<div class="poll_result_bar"></div>
				{/if}
			</div>
		{/foreach}
	{/foreach}
	{if $element->getVoteCount()>0}
		<div class="poll_results_total">{translations name='poll.voters'}: {$element->getVoteCount()}</div>
	{/if}
{/capture}

{assign moduleClass "poll_results_block"}
{assign moduleTitleClass "poll_results_title"}

{include file=$theme->template("component.columnmodule.tpl")}