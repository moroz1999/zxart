{if !isset($item.textClass)}
    {$item.textClass = ''}
{/if}
{if !empty($item.translationGroup)}
{$translationGroup = $item.translationGroup}
{else}
	{$translationGroup = $structureType}
{/if}
{assign var="labelBefore" value=""}
{assign var="labelAfter" value=""}
{assign var="formFieldCols" value=""}
{assign var='primColor' value=''}
{if !empty($item.additionalFormat)}
	{if !empty($item.additionalFormat.labelBefore)}
		{$labelBefore = $item.additionalFormat.labelBefore}
	{else}
		{$labelBefore = $fieldName}
	{/if}
    {$labelAfter  = $item.additionalFormat.labelAfter}
	{$formFieldCols = ' labelAfter'}
{else}
	{$labelBefore = $fieldName}
{/if}
{if !empty($item.valuesTranslationGroup)}
	{$valuesTranslationGroup = $item.valuesTranslationGroup}
{else}
	{$valuesTranslationGroup = $structureType}
{/if}
{if !empty($item.inputDefaultValueMethod) && empty($formData.$fieldName)}
	{$defaultValue = $form->callElementMethod($item.inputDefaultValueMethod['method'], $item.inputDefaultValueMethod['variable'])}
	{$formData.$fieldName = $defaultValue}
{/if}
<div class="form_items{if $formErrors.$fieldName} form_error{/if}{if !empty($item.trClass)} {$item.trClass} {/if}">
		<span class="form_label">
			{translations name="{$translationGroup}.{$labelBefore}"}
		</span>
        <div class="form_field{$formFieldCols}">
			<input class="input_component{if $item.textClass} {$item.textClass}{/if}"{if !empty($item.stepValue)} step="{$item.stepValue}"{/if}{if !empty($item.minValue)} min="{$item.minValue}"{/if}{if !empty($item.maxValue)} max="{$item.maxValue}"{/if}  type="{if !empty($item.inputType)}{$item.inputType}{else}text{/if}" value="{$formData.$fieldName}"
		   name="{$formNames.$fieldName}"/>
			{if !empty($labelAfter)}
				<span class="form_label_after">
				{translations name="{$valuesTranslationGroup}.{$labelAfter}"}
				</span>
			{/if}
		</div>
        <div class="form_helper">
			{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name=$fieldName}
		</div>
</div>
