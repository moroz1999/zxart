{if isset($currentElementPrivileges.showForm)}
	<a href="{$currentElement->URL}id:{$currentElement->id}/action:showForm/" class="tabs_list_item tabs_list_item_static{if $currentElement->actionName == 'showForm'} tabs_list_active{/if}"><span class="tabs_list_item_center"><span class='icon icon_edit'></span>{translations name='label.modify'}</span></a>
{/if}
{if $element->hasActualStructureInfo()}
	{if isset($currentElementPrivileges.showFullList)}
		<a href="{$currentElement->URL}id:{$currentElement->id}/action:showFullList/" class="tabs_list_item tabs_list_item_static{if $currentElement->actionName == 'showFullList'} tabs_list_active{/if}"><span class="tabs_list_item_center"><span class='icon icon_list'></span>{translations name='label.content'}</span></a>
	{/if}
	{if isset($currentElementPrivileges.showPrivileges)}
		<a href="{$currentElement->URL}id:{$currentElement->id}/action:showPrivileges/" class="tabs_list_item tabs_list_item_static{if $currentElement->actionName == 'showPrivileges'} tabs_list_active{/if}"><span class="tabs_list_item_center">{translations name='label.privileges'}</span></a>
	{/if}
{/if}