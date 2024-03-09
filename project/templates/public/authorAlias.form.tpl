{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="authoralias_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr{if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='authoralias.title'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>
		<tr {if $formErrors.startDate} class="form_error"{/if}>
			<td class="form_label">
				{translations name='authoralias.startdate'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.startDate}" name="{$formNames.startDate}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="startDate"}
			</td>
		</tr>
		<tr {if $formErrors.endDate} class="form_error"{/if}>
			<td class="form_label">
				{translations name='authoralias.enddate'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.endDate}" name="{$formNames.endDate}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="endDate"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='authoralias.displayinmusic'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.displayInMusic}"{if $element->displayInMusic} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="displayinmusic"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='authoralias.displayingraphics'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.displayInGraphics}"{if $element->displayInGraphics} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="displayingraphics"}
			</td>
		</tr>
		<tr {if $formErrors.authorId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='authoralias.author'}:
			</td>
			<td class="form_field">
				<select class="authoralias_form_author_select" name="{$formNames.authorId}" autocomplete='off'>
					{assign var="authorElement" value=$element->getAuthorElement()}
					{if $authorElement}
						<option value='{$authorElement->id}' selected="selected">
							{$authorElement->title}
						</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="authorId"}
			</td>
		</tr>

	</table>
	{if $element->hasActualStructureInfo()}
		{include file=$theme->template('component.controls.tpl') action='publicReceive'}
	{else}
		{include file=$theme->template('component.controls.tpl') action='publicAdd'}
	{/if}
</form>
