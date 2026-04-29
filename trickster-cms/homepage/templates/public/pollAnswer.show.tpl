{if $multiChoice}
	<div class="poll_option">
		<input class="checkbox_placeholder" name="{$formNames.answers}[{$questionId}][]" value="{$element->id}" type="checkbox" />
		<label for="answer_{$element->id}" class="answer_text">{$element->answerText}</label>
	</div>
{else}
	<div class="poll_option">
		<input id="answer_{$element->id}" class="radio_holder" name="{$formNames.answers}[{$questionId}][]" value="{$element->id}" type="radio" />
		<label for="answer_{$element->id}" class="answer_text">{$element->answerText}</label>
	</div>
{/if}
