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

<div class="form_items">
	<div class="form_label">
		{translations name="{$translationGroup}.{$fieldName}"}
	</div>
	<div class="form_field">
		<select class="{if !empty($item.class)}{$item.class} {/if}dropdown_placeholder" name="{$formNames.$fieldName}" autocomplete='off'>
			{if !empty($item.defaultRequired)}
				<option value="">{if !empty($item.defaultName)}{$item.defaultName}{/if}</option>
			{/if}
			{if !empty($options) && is_array($options)}
				{foreach $options as $option}
					<option value="{$option}"{if $formData.$fieldName == $option} selected="selected"{/if}>
						{if is_numeric({$option})}
							{$option}
						{else}
							{translations name="{$translationGroup}.{$option}"}
						{/if}
					</option>
				{/foreach}
			{/if}
		</select>
	</div>
	{include file=$theme->template('component.form_help.tpl')}
</div>