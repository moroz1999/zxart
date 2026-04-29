<div class="tabs_block">
	<div class="tabs_list">
		{include file=$theme->template('shared.tabs.tpl')}
	</div>
	<div class="tabs_content">
		{if $contentSubTemplate}
			{include file=$theme->template($contentSubTemplate)}
		{/if}
	</div>
</div>