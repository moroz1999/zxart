{$leftColumnElements = $currentLanguage->getLeftColumnElementsList()}
{$rightColumnElements = $currentLanguage->getRightColumnElementsList()}
<div class="columns_table grid_md">
	{if $currentLanguage->getLeftColumnElementsList()}
		{include file=$theme->template('component.column.left.tpl')}
	{/if}
	{include file=$theme->template('component.column.center.tpl')}
	{*{if $currentLanguage->getRightColumnElementsList()}*}
		{include file=$theme->template('component.column.right.tpl')}
	{*{/if}*}
</div>