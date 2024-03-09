{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.authorId} class="form_error"{/if}>
			<td>
				{translations name='field.author'}:
			</td>
			<td colspan='2'>
				<select class="dropdown_placeholder" name="{$formNames.authorId}" autocomplete='off'>
					<option value=''></option>
					{foreach from=$element->authorsList item=letter}
						<optgroup label="{$letter->title}">
							{foreach from=$letter->childrenList item=author}
								{assign var='authorId' value=$author->id}
								<option value='{$author->id}' {if $author->id == $formData.authorId}selected="selected"{/if}>
									{$author->title}
								</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.wosId} class="form_error"{/if}>
			<td>
				WOS ID:
			</td>
			<td>
				<input class='input_component' type="text" name="{$formNames.wosId}" value="" />
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl') action="import"}
</form>