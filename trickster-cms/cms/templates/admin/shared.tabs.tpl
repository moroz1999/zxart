{if $tabsTemplate = $element->getTabsTemplate(false)}
	{include file=$theme->template($tabsTemplate)}
{else}
	{foreach $element->getTabs() as $tab}
			{assign 'action' $tab->getName()}
			<a href="{$tab->getUrl()}" class="tabs_list_item tabs_list_item_static{if $tab->isActive()} tabs_list_active{/if}">
				<span class="tabs_list_item_center">
				{if $icon = $tab->getIcon()}
					<span class='icon {$icon}'></span>
				{else}
					<span class='icon icon_list'></span>
				{/if}
				{if $label = $tab->getLabel()}
					{translations name=$label}
				{else}visitors_table
					{translations name="label.{$action}"}
				{/if}
			</span>
			</a>
	{/foreach}
{/if}