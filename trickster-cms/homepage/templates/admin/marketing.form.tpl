{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		{foreach from=$formData.title key=languageId item=title}
		<tr{if $formErrors.title.$languageId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.heading'} ({$languageNames.$languageId})
			</td>
			<td>
				<input class='input_component' type="text" value="{$title}" name="{$formNames.title.$languageId}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>
		{/foreach}
		<tr{if $formErrors.marker} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.elementmarker'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.marker}" name="{$formNames.marker}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="marker"}
			</td>
		</tr>
	</table>
	{include file=$theme->template('component.controls.tpl')}
</form>
