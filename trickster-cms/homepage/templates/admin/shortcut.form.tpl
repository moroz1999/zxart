{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		<tr{if $formErrors.title} class="form_error"{/if}>
			<td>
				{translations name='field.heading'}:
			</td>
			<td colspan='2'>
				<input type="text" value="{$formData.title}" name="{$formNames.title}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>
		<tr{if $formErrors.target} class="form_error"{/if}>
			<td>
				{translations name='field.targetelement'}:
			</td>
			<td colspan='2'>
				<select name="{$formNames.target}" autocomplete='off'>
					<option value='all'>{translations name='label.all'}</option>
					{foreach from=$element->elementsList item=listItem}
						<option {if $listItem.id == $element->target}selected='selected'{/if}style='padding-left: {$listItem.level*20}px' value='{$listItem.id}'>
							{$listItem.title}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="target"}
			</td>
		</tr>
	</table>
	<input type="submit" value="{translations name='button.save'}"/>
	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="receive" name="action" />
</form>
