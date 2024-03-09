{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component zxProdCategoriescatalogue_form" method="post" enctype="multipart/form-data">
	<div class="tabs_content_item">
		<table class='form_table'>
			<tr{if $formErrors.title} class="form_error"{/if}>
				<td class="form_label">
					{translations name='zxProdCategoriescatalogue.title'}:
				</td>
				<td colspan='2'>
					<input class="input_component" type="text" value="{$formData.title}" name="{$formNames.title}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
				</td>
			</tr>
		</table>
		{include file=$theme->template('block.controls.tpl')}
	</div>
</form>
