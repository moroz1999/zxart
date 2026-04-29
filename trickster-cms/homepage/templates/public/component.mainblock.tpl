<div class="main_block{if !empty($currentMainMenu) && $currentMainMenu->marker} {$currentMainMenu->marker}_element{/if}">
	{include file=$theme->template('component.header.tpl')}
	{include file=$theme->template('component.center.tpl')}
	{include file=$theme->template('component.footer.tpl')}
</div>