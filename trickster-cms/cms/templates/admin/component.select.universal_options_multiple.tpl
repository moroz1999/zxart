{if isset($item.method)}
	{assign var='options' value=$form->callElementMethod($item.method)}
{elseif isset($item.property)}
	{assign var='options' value=$form->getElementProperty($item.property)}
{else}
	{assign var='options' value=""}
{/if}
{if isset($item.condition)}
	{assign var='condition' value=$form->callElementMethod($item.condition)}
{else}
	{assign var='condition' value=true}
{/if}
{if isset($item.translationGroup)}
	{assign var='translationGroup' value=$item.translationGroup}
{/if}

{if $condition}
	<div class="form_items{if $formErrors.$fieldName} form_error{/if}{if !empty($item.trClass)} {$item.trClass}{/if}">
	<span class="form_label">
		{translations name="{$translationGroup}.{$fieldName}"}
	</span>
		<div class="form_field">
			<select class="{if !empty($item.class)}{$item.class} {/if}select_multiple"{if !empty($item.dataset)} {$item.dataset[0]}="{$item.dataset[1]}" {/if}multiple="multiple" name="{$formNames.$fieldName}[]" autocomplete='off'>
				<option value=''></option>
				{if is_array($options)}
					{foreach $options as $option}
						<option value='{$option.id}'{if !empty($option.select)} selected="selected"{/if}>
							{if !empty($option.level)}{section name="level" start=0 loop=$option.level}&nbsp;&nbsp;{/section}{/if}
								{$option.title}{if !empty($option.group_title)} ({$option.group_title}){/if}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
		{include file=$theme->template('component.form_help.tpl')}
	</div>
{/if}