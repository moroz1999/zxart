{assign var="questionId" value=$element->id}
<div class="poll_question_text{if $formErrors.answers.$questionId} poll_show_question_error" role="alert{/if}">{$element->questionText}</div>
<div class="poll_options">
	{foreach from=$question->getAnswersList() item=answer name=answers}
		{include file=$theme->template($answer->getTemplate()) element=$answer questionId=$question->id multiChoice=$element->multiChoice}
	{/foreach}
</div>