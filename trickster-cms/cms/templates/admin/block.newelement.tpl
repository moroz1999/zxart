{if isset($allowedTypes) && is_array($allowedTypes) && count($allowedTypes)}
	{if !isset($newElementUrl)}
		{assign "newElementUrl" $currentElement->getNewElementUrl()}
	{/if}
	{if !isset($action)}
		{assign var="action" value="showForm"}
	{/if}
	{if method_exists($element, 'getNewElementAction')}
        {$action = $element->getNewElementAction()}
	{/if}
	{if !isset($buttonId)}
		{assign var="buttonId" value="addnewelement1"}
	{/if}
	<script>
		addNewElementInfo = [];
		{foreach from=$allowedTypes item=type}
		{assign var='typeLowered' value=$type|strtolower}
		{assign var='translationName' value="element."|cat:$typeLowered}
		addNewElementInfo.push({ldelim}
			'url': "{$newElementUrl}type:{$type}/action:{$action}/",
			'name': "{translations name=$translationName}",
			'icon': "icon icon_{$type}"
			{rdelim});
		{/foreach}
		if (!window.addNewElementInfoButtons) {
			window.addNewElementInfoButtons = { } ;
		}
		addNewElementInfoButtons['{$buttonId}'] = addNewElementInfo;

	</script>
	<button type="button" id="{$buttonId}" class='button primary_button addnewelement_button'>
		<span class="icon icon_addnew"></span>
		<span class="addnew_label">{translations name="label.addnew"}</span>
	</button>
{/if}
