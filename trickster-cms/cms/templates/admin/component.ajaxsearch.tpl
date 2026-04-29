{if isset($item.method)}
	{assign var='content' value=$form->callElementMethod($item.method)}
{elseif isset($item.property)}
	{assign var='content' value=$form->getElementProperty($item.property)}
{else}
	{assign var='content' value=""}
{/if}

<div class="form_items">
	<div class="form_label">
		{translations name="{$structureType}.{$fieldName}"}
	</div>
	<div class="form_field">
		<select class="{if !empty($item.class)}{$item.class}{/if}" name="{$formNames.$fieldName}" autocomplete='off' {if !empty($item.types)}data-types="{$item.types}{/if}">
			{if !empty($content)}
				<option value='{$content->id}' selected="selected">
					{$content->getTitle()}
				</option>
			{/if}
		</select>
	</div>
</div>

