{if isset($item.method)}
	{assign var='options' value=$form->callElementMethod($item.method)}
{elseif isset($item.property)}
	{assign var='options' value=$form->getElementProperty($item.property)}
{elseif isset($item.options)}
	{assign var='options' value=$item.options}
{/if}
{if !empty($item.translationGroup)}
	{$translationGroup = $item.translationGroup}
{else}
	{$translationGroup = $structureType}
{/if}
{assign var='currentStatus' value=''}
{assign var='currentSelected' value=''}
{assign var='additionalCell' value=[]}
{if isset($item.additionalCell)}
	{$additionalCell = $item.additionalCell}
{/if}
<div class="form_items{if !empty($item.trClass)} {$item.trClass}{/if}">
	<div class="form_label">
		{translations name="{$structureType}.{$fieldName}"}
	</div>
	<div class="form_field">
		<select class="{if !empty($item.class)}{$item.class} {/if}dropdown_placeholder" name="{$formNames.$fieldName}" autocomplete='off'>
			{if !empty($item.defaultRequired)}
				<option value=""></option>
			{/if}
			{if is_array($options)}
				{foreach $options as $value=>$title}
					{if $formData.$fieldName == $value}
						{$currentStatus = $value}
						{$currentSelected = ' selected="selected"'}
					{/if}
					<option value="{$value}"{$currentSelected}>
						{if is_numeric($title)}
							{$title}
						{else}
							{translations name="{$translationGroup}.{$title}"}
						{/if}
					</option>
					{$currentSelected = ''}
				{/foreach}
			{/if}
		</select>
	</div>
	{if !empty($additionalCell)}
		{assign var='additionalFieldName' value=''}
		{if !empty($additionalCell['additionalFieldName'])}
			{$additionalFieldName = $additionalCell['additionalFieldName']}
		{/if}
		{if !empty($additionalCell['template'])}
			{$additionalTemplate=$additionalCell['template']}
		<div class="form_field">
			{include file=$theme->template($additionalTemplate) additionalFieldName = $additionalFieldName orderStatus = $currentStatus}
		</div>
		{/if}
	{/if}
	{include file=$theme->template('component.form_help.tpl')}
</div>