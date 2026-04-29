{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
{assign var='structureType' value=$element->structureType}

{if $form->getContainerClass()}
	<div class="{$form->getContainerClass()}">
{/if}
{if $form->getFormComponents()}
	{$translationGroup = $form->getTranslationGroup()}
	<form action="{$form->getFormAction()}" class="form_component {$form->getFormClass()}" method="{$form->getFormMethod()}" enctype="{$form->getFormEnctype()}">
		<div class="{$form->getPreset()} form_fields">
			{foreach from=$form->getFormComponents() item=item key=fieldName}
				{include file=$theme->template("component.{$item.type}.tpl") fieldName = $fieldName item=$item translationGroup=$translationGroup}
			{/foreach}
			{if $form->getCustomComponent()}
				{include file=$theme->template("{$form->getCustomComponent()}.tpl") fieldName = $fieldName}
			{/if}
		</div>
		{if $controls = $form->getControlsLayout()}
			{include file=$theme->template($controls) fieldName = $fieldName}
		{/if}
	</form>
{/if}
{if $additionaContentTemplate = $form->getAdditionalContent()}
	{include file=$theme->template($additionaContentTemplate)}
{/if}
{if $form->getContainerClass()}
	</div>
{/if}