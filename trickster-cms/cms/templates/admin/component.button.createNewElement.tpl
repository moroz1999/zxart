{if $currentElement->getAllowedTypes($currentElement->getActionName())}
	{$allowedTypes = $currentElement->getAllowedTypes($currentElement->getActionName())}
    {if !isset($newElementUrl)}
        {assign "newElementUrl" $currentElement->getNewElementUrl()}
    {/if}

    {if !isset($item.action)}
        {assign var="actionName" value="showForm"}
    {/if}
    {if !isset($buttonId)}
        {assign var="buttonId" value="addnewelement1"}
    {/if}
	<div class="form_items">
		<div class="form_label"></div>
		<div class="form_field">
			<script>
				addNewElementInfo = [];
                {foreach from=$allowedTypes item=type}
                {assign var='typeLowered' value=$type|strtolower}
                {assign var='translationName' value="element."|cat:$typeLowered}
				addNewElementInfo.push({ldelim}
					'url': "{$newElementUrl}type:{$type}/action:{$actionName}/",
					'name': "{translations name=$translationName}",
					'icon': "icon icon_{$type}"
                    {rdelim});
                {/foreach}
				if (!window.addNewElementInfoButtons) {
					window.addNewElementInfoButtons = {};
				}
				addNewElementInfoButtons['{$buttonId}'] = addNewElementInfo;

			</script>
			<button type="button" id="{$buttonId}" class='button primary_button addnewelement_button'>
				<span class="icon icon_addnew"></span>
				<span class="addnew_label">{translations name="label.addnew"}</span>
			</button>
		</div>
	</div>
{/if}
