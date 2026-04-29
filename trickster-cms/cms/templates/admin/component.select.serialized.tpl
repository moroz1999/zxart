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
{if !empty($item.valuesTranslationGroup)}
	{$valuesTranslationGroup = $item.valuesTranslationGroup}
{else}
	{$valuesTranslationGroup = $structureType}
{/if}

{if isset($item.condition)}
	{assign var='condition' value=$form->callElementMethod($item.condition)}
{else}
	{assign var='condition' value=true}
{/if}


{if $condition}
	<div class="form_items{if $formErrors.$fieldName} form_error{/if}{if !empty($item.trClass)} {$item.trClass}{/if}">
	<span class="form_label">
		{translations name="{$translationGroup}.{$fieldName}"}
	</span>
		<div class="form_field">
			<select class="{if !empty($item.class)}{$item.class} {/if}select_multiple" multiple="multiple" name="{$formNames.$fieldName}[]" autocomplete='off'>
				{if is_array($options)}
					{foreach $options as $value=>$title}
						<option value="{$title}" {if $title|in_array:$formData.$fieldName}selected="selected"{/if}>
							{if is_numeric($title)}
								{$title}
							{else}
								{translations name="{$valuesTranslationGroup}.{$title}"}
							{/if}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
		{include file=$theme->template('component.form_help.tpl')}
	</div>
{/if}