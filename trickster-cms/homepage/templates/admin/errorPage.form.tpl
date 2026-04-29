{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component" method="post" enctype="multipart/form-data" role="form">
	<div class="form_fields">
		{foreach from=$formData.content key=languageId item=content}
			<div class="form_items{if $formErrors.content.$languageId} form_error{/if}">
				<span class="form_label">
					{translations name='field.content'} ({$languageNames.$languageId}):
				</span>
				<div class="form_field">
					{include file=$theme->template('component.htmleditor.tpl') data=$content name=$formNames.content.$languageId}
				</div>
				<div class="form_helper">{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="content"}
				</div>
			</div>
		{/foreach}
	</div>
	{include file=$theme->template('component.controls.tpl') action='receiveErrorPageForm'}
</form>
