{if isset($currentElementPrivileges.import)}
	{assign var='formData' value=$element->getFormData()}
	{assign var='formErrors' value=$element->getFormErrors()}
	{assign var='formNames' value=$element->getFormNames()}

	<form class="export_import_form form_component" action="{$currentElement->URL}id:{$currentElement->id}/action:import/" method="post" enctype="multipart/form-data">
		<div class="form_fields">
			<div class="form_items"{if $formErrors.xmlFile} class="form_error"{/if}>
				<span class="form_label">
					{translations name='field.uploadfile'}:
				</span>
				<div class="form_field">
					<input class="fileinput_placeholder" type="file" name="{$formNames.xmlFile}"/>
					<input class="actions_form_import button" type="submit" value='{translations name='button.upload'}'/>
				</div>
			</div>
		</div>
	</form>
{/if}