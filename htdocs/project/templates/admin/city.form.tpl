{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component city_form" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		{if is_array($formData.title)}
		{foreach from=$formData.title key=languageId item=title}
		<tr{if $formErrors.title.$languageId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.heading'} ({$languageNames.$languageId}):
			</td>
			<td>
				<input class='input_component' type="text" value="{$title}" name="{$formNames.title.$languageId}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>
		{/foreach}
			{else}
			<tr{if $formErrors.title} class="form_error"{/if}>
				<td class="form_label">
					{translations name='field.heading'}:
				</td>
				<td colspan='2'>
					<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
				</td>
			</tr>
		{/if}
		<tr class="{if $formErrors.joinCity} form_error{/if}">
			<td class="form_label">
				{translations name='city.joincity'}:
			</td>
			<td>
				<select class="city_form_jointag_select" name="{$formNames.joinCity}" autocomplete='off'></select>
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>
