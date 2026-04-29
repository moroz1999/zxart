{if isset($allowedTypes)}
	{include file=$theme->template("block.newelement.tpl") allowedTypes=$allowedTypes}
{/if}

{if isset($actionButtons)}
	{*custom buttons for element*}
	{foreach $actionButtons as $buttonInfo}
		{$actionName=$buttonInfo.action}
		<button type="button" class="actions_form_button button{if isset($buttonInfo.class)} {$buttonInfo.class}{/if}"
				data-action="{$buttonInfo.action}" {if isset($buttonInfo.targetId)} data-targetid="{$buttonInfo.targetId}"{/if}{if isset($buttonInfo.confirmation)} data-confirmation="{$buttonInfo.confirmation}"{/if}>{if isset($buttonInfo.icon)}
			<span class="icon icon_{$buttonInfo.icon}">
				</span>{/if}{$buttonInfo.text}
		</button>
	{/foreach}
{else}
	{*default buttons*}
	{if isset($rootPrivileges.copyElements)}
		<button type="button" class="actions_form_button button" data-url="{$rootElement->getFormActionURL()}" data-action="copyElements">
			<span class="icon icon_copy"></span>
			{translations name="button.copyselected"}
		</button>
	{/if}
	{if isset($rootPrivileges.moveElements)}
		<button type="button" class="actions_form_button button" data-url="{$rootElement->getFormActionURL()}" data-action="moveElements">
			<span class="icon icon_move"></span>
			{translations name="button.moveselected"}
		</button>
	{/if}
	{if isset($rootPrivileges.deleteElements)}
		<button type="button" class="actions_form_button button warning_button" data-url="" data-action="deleteElements" data-confirmation="{translations name="message.deleteselectedconfirm"}">
			<span class="icon icon_delete"></span>
			{translations name="button.deleteselected"}
		</button>
	{/if}
	{if $currentElement->structureType == 'catalogue' || $currentElement->structureType == 'category'}
		{if isset($rootPrivileges.cloneElements)}
			<button type="button" class="actions_form_button button"
					data-url="" data-action="cloneElements">
				<span class="icon icon_clone"></span>
				{translations name="button.cloneselected"}
			</button>
		{/if}
	{/if}
	{if isset($currentElementPrivileges.xlsExport)}
		<a class="actions_form_button button"
		   href="{$element->getExportLink()}">
			<span class="icon icon_export"></span>
			{translations name="catalogue.exportxlsx"}
		</a>
	{/if}
{/if}