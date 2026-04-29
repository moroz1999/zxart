{if $element->getPollElement()}
	{include file=$theme->template($element->getPollElement()->getTemplate()) element=$element->getPollElement()}
{/if}