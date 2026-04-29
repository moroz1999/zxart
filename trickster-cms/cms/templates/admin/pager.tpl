{if $pager && count($pager->pagesList)>1}
	<div class='pager_block'>
		<div class="pager">
			<a href='{if $pager->previousPage.active}{$pager->previousPage.URL}{/if}' class='pager_previous {if !$pager->previousPage.active}pager_hidden{/if}'></a>
			{foreach from=$pager->pagesList item=page}
				{if $page.number === false}
					<span class='pager_page'>...</span>
				{elseif $page.active}
					<span class='pager_page pager_active'>{$page.number}</span>
				{elseif $page.URL}
					<a href='{$page.URL}' class='pager_page'>{$page.number}</a>
				{/if}
			{/foreach}
			<a href='{if $pager->nextPage.active}{$pager->nextPage.URL}{/if}' class='pager_next {if !$pager->nextPage.active}pager_hidden{/if}'></a>
		</div>
	</div>
{/if}