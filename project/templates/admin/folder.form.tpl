{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component" method="post" enctype="multipart/form-data" >

		<table class="form_table">
			<tr{if $formErrors.title} class="form_error"{/if}>
				<td class="form_label">
				{translations name='field.heading'}:
				</td>
				<td>
					<input class="focused_input input_component" type="text" value="{$formData.title}" name="{$formNames.title}" />
				</td>
				<td>
					<a href="" class="form_helper"></a>
				</td>
			</tr>
			<tr>
				<td class="form_label">
					{translations name='field.hidden'}:
				</td>
				<td>
					<input class="checkbox_placeholder" type="checkbox" value="1" name="{$formNames.hidden}" {if $formData.hidden == '1'}checked="checked"{/if} />
				</td>
				<td>
					<a href="" class="form_helper_active"></a>
				</td>
			</tr>
			<tr{if $formErrors.columns} class="form_error"{/if}>
				<td class="form_label">
				{translations name='field.columns'}:
				</td>
				<td>
					<select name="{$formNames.columns}" class="dropdown_placeholder">
						<option value='left' {if $formData.columns=='left'}selected='selected'{/if}>{translations name='selector.columns_left'}</option>
						<option value='right' {if $formData.columns=='right'}selected='selected'{/if}>{translations name='selector.columns_right'}</option>
						<option value='both' {if $formData.columns=='both'}selected='selected'{/if}>{translations name='selector.columns_both'}</option>
						<option value='none' {if $formData.columns=='none'}selected='selected'{/if}>{translations name='selector.columns_none'}</option>
					</select>
				</td>
			</tr>

			<tr{if $formErrors.marker} class="form_error"{/if}>
				<td class="form_label">
					{translations name='field.marker'}:
				</td>
				<td>
					<select name="{$formNames.marker}" class="dropdown_placeholder">
						<option value='' {if $formData.marker==''}selected='selected'{/if}>{translations name='field.notselected'}</option>
						<option value='news' {if $formData.marker=='news'}selected='selected'{/if}>news</option>
						<option value='partiesmenu' {if $formData.marker=='partiesmenu'}selected='selected'{/if}>partiesmenu</option>
						<option value='authorsmenu' {if $formData.marker=='authorsmenu'}selected='selected'{/if}>authorsmenu</option>
						<option value='gamesmenu' {if $formData.marker=='gamesmenu'}selected='selected'{/if}>gamesmenu</option>
						<option value='music' {if $formData.marker=='music'}selected='selected'{/if}>music</option>
						<option value='graphics' {if $formData.marker=='graphics'}selected='selected'{/if}>graphics</option>
						<option value='comments' {if $formData.marker=='comments'}selected='selected'{/if}>comments</option>
						<option value='groupsmenu' {if $formData.marker=='groupsmenu'}selected='selected'{/if}>groupsmenu</option>
					</select>
				</td>
			</tr>
			<tr{if $formErrors.image} class="form_error"{/if}>
				<td class="form_label">
				{translations name='field.image'}:
				</td>
				<td>
				{if $element->originalName != ""}
					<img class="form_image" loading="lazy" src='{$controller->baseURL}image/type:adminImage/id:{$element->image}/filename:{$element->originalName}' />
					<a href="{$element->URL}id:{$element->id}/action:deleteFile/file:image/">{translations name='label.deleteimage'}</a>
					{else}
					<input type="file" class="fileinput_placeholder" name="{$formNames.image}" />
				{/if}
				</td>
			</tr>

			<tr{if $formErrors.externalUrl} class="form_error"{/if}>
				<td class="form_label">
				{translations name='field.externallink'}:
				</td>
				<td>
					<input class='input_component' type="text" value="{$formData.externalUrl}" name="{$formNames.externalUrl}" />
				</td>
			</tr>
			{include file=$theme->template('component.displaymenus_selector.tpl')}
		</table>
		{include file=$theme->template('block.controls.tpl')}
</form>
