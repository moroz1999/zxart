{logMessage message="Deprecated template used: component.mainmenu.tpl"}
{*** please use subMenuList.header.tpl instad of this ***}
{stripdomspaces}
{if !isset($mainMenu)}
	{$mainMenu=$currentLanguage->getMainMenuElements()}
{/if}
{if $mainMenu}
<nav class='menu_block' role="navigation">
	<div class='menu_content_block' role="menu">
	{foreach from=$mainMenu item=menu name=mainmenu}
		{if !$menu->hidden}
		<a href="{$menu->URL}" class="menuitem_block{if $menu->requested} menuitem_active{/if} menuid_{$menu->id}">
			<span class='menuitem_title' role="menuitem">{$menu->title}</span>
		</a>
		{/if}
	{/foreach}
	</div>
</nav>
{/if}
{/stripdomspaces}