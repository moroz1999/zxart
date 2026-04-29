{assign var='formNames' value=$element->getFormNames()}
{assign var='formErrors' value=$element->getFormErrors()}

{capture assign="moduleContent"}
	{if $element->title}
		{capture assign="moduleTitle"}
			{$element->title}
		{/capture}
	{/if}
	{if $element->description}
		<div class="block_desc">{$element->description}</div>
	{/if}

	<form action="{$currentElement->getFormActionURL()}" class='poll_form' method="post" enctype="multipart/form-data" role="form">
        <input type="hidden" value="{$element->id}" name="id" />
        <input type="hidden" value="submitVote" name="action" />
		{foreach from=$element->getQuestionsList() item=question name=questions}
			{include file=$theme->template($question->getTemplate()) element=$question}
		{/foreach}
		<div class="poll_submit"><input class="button" type="submit" value="{translations name='poll.vote'}"/></div>
	</form>
{/capture}

{assign moduleClass "poll_show_block"}
{assign moduleTitleClass "poll_title"}

{include file=$theme->template("component.columnmodule.tpl")}
