{if isset($item.method)}
	{assign var='options' value=$form->callElementMethod($item.method)}
{elseif isset($item.property)}
	{assign var='options' value=$form->getElementProperty($item.property)}
{elseif isset($item.options)}
	{assign var='options' value=$item.options}
{/if}

<div class="form_items">
	<span class="form_label">
		{translations name="{$structureType}.{$fieldName}"}
	</span>
	<div class="form_field">
		<select class="{if !empty($item.class)}{$item.class} {/if}dropdown_placeholder" name="{$formNames.$fieldName}" autocomplete='off'>
			{if !empty($item.defaultRequired)}
				<option value=""></option>
			{/if}
			{if is_array($options)}
				{foreach $options as $option}
					<option value="{$option.id}"{if !empty($option.select)} selected="selected"{/if}>{if !empty($option.level)}{section name="level" start=0 loop=$option.level}&nbsp;&nbsp;{/section}{/if}{$option.title}</option>
				{/foreach}
			{/if}
		</select>
	</div>
	{include file=$theme->template('component.form_help.tpl')}
</div>