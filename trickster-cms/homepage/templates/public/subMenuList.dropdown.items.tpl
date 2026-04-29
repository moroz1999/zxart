{stripdomspaces}
{foreach $subMenus as $subMenu}
	<option class="submenu_item submenu_item_level_{$level}{if $subMenu->requested} submenu_item_active{/if}" value='{$subMenu->URL}'{if $subMenu->final} selected="selected"{/if} role="menuitem">
		{section name=padding start=0 loop=$level-1}>>{/section} {$subMenu->title}
	</option>
	{if $level < $levels || ($subMenu->requested && $level < $element->maxLevels)}
		{if $subMenu->getSubMenuList()}
			{include file=$theme->template("subMenuList.dropdown.items.tpl") level=$level+1 subMenus=$subMenu->getSubMenuList()}
		{/if}
	{/if}
{/foreach}
{/stripdomspaces}