{if $pager && count($pager->pagesList)>1}
<div class='pager_block'>
	<a href='{if $pager->previousPage.active}{$pager->previousPage.URL}{/if}' class='button button_transparent button_square {if !$pager->previousPage.active}pager_disabled{/if}'>&lt;</a>
	{foreach from=$pager->pagesList item=page}
		{if $page.number === false}
			<span class='button button_transparent button_square pager_disabled'>...</span>
		{elseif $page.active}
			<span class='button button_primary button_square'>{$page.number}</span>
		{elseif $page.URL}
			<a href='{$page.URL}' class='button button_transparent button_square'>{$page.number}</a>
		{/if}
	{/foreach}
	<a href='{if $pager->nextPage.active}{$pager->nextPage.URL}{/if}' class='button button_transparent button_square {if !$pager->nextPage.active}pager_disabled{/if}'>&gt;</a>
</div>
{/if}
