{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.title'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>
		<tr {if $formErrors.party} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.party'}:
			</td>
			<td class="form_field">
				<select class="zxitem_form_party_select" name="{$formNames.party}" autocomplete='off'>
					{assign var="partyElement" value=$element->getPartyElement()}
					{if $partyElement}
						<option value='{$partyElement->id}' selected="selected">
							{$partyElement->title}
						</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="party"}
			</td>
		</tr>
		<tr {if $formErrors.partyplace} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.partyplace'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.partyplace}" name="{$formNames.partyplace}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="partyplace"}
			</td>
		</tr>
		<tr {if $formErrors.compo} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.compo'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.compo}" >
					<option value=''></option>
					{foreach $element->getCompoTypes() as $compoType}
						<option value='{$compoType}' {if $compoType == $formData.compo}selected='selected'{/if}>{translations name="zxPicture.compo_{$compoType}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="compo"}
			</td>
		</tr>
		<tr {if $formErrors.author} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.author'}:
			</td>
			<td class="form_field">
				<select class="select_multiple zxitem_form_authors_select" multiple="multiple" name="{$formNames.author}[]" autocomplete='off'>
					<option value=''></option>
					{foreach from=$element->getAuthorsList() item=author}
						<option value='{$author->id}' selected="selected">
							{$author->getSearchTitle()}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="author"}
			</td>
		</tr>
		<tr {if $formErrors.originalAuthor} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxPicture.original_author'}:
			</td>
			<td class="form_field">
				<select class="select_multiple zxitem_form_authors_select" multiple="multiple" name="{$formNames.originalAuthor}[]" autocomplete='off'>
					<option value=''></option>
					{foreach from=$element->getOriginalAuthorsList() item=originalAuthor}
						<option value='{$originalAuthor->id}' selected="selected">
							{$originalAuthor->getSearchTitle()}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="originalAuthor"}
			</td>
		</tr>
		<tr {if $formErrors.game} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.release'}:
			</td>
			<td class="form_field">
				<select class="zxitem_form_prodrelease_select" name="{$formNames.game}" autocomplete='off'>
					{assign var="releaseElement" value=$element->getReleaseElement()}
					{if $releaseElement}
						<option value='{$releaseElement->id}' selected="selected">
							{$releaseElement->title}
						</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="game"}
			</td>
		</tr>
		<tr {if $formErrors.year} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.year'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.year}" name="{$formNames.year}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="year"}
			</td>
		</tr>
		<tr {if $formErrors.tagsText} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.tagstext'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.tagsText}" name="{$formNames.tagsText}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="tagstext"}
			</td>
		</tr>
		<tr {if $formErrors.palette} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.palette'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.palette}" >
					<option value="">{translations name="zxpicture.palette_auto"}</option>
					{foreach $element->getPaletteTypes() as $paletteType}
						<option value='{$paletteType}' {if $paletteType == $formData.palette}selected='selected'{/if}>{translations name="zxpicture.palette_{$paletteType}"}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.border} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.border'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.border}" >
					<option value='0' {if $formData.border=='0'}selected='selected'{/if}>Black</option>
					<option value='1' {if $formData.border=='1'}selected='selected'{/if}>Blue</option>
					<option value='2' {if $formData.border=='2'}selected='selected'{/if}>Red</option>
					<option value='3' {if $formData.border=='3'}selected='selected'{/if}>Magenta</option>
					<option value='4' {if $formData.border=='4'}selected='selected'{/if}>Green</option>
					<option value='5' {if $formData.border=='5'}selected='selected'{/if}>Cyan</option>
					<option value='6' {if $formData.border=='6'}selected='selected'{/if}>Yellow</option>
					<option value='7' {if $formData.border=='7'}selected='selected'{/if}>White</option>
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="border"}
			</td>
		</tr>
		<tr {if $formErrors.type} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.type'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.type}" >
					{foreach $element->getZxPictureTypes() as $type=>$translation}
						<option value="{$type}" {if $formData.type === $type}selected="selected"{/if}>
							{translations name=$element->getZxPictureTypeTranslation($type)}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="type"}
			</td>
		</tr>
		<tr {if $formErrors.sequence} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.sequence'}:
			</td>
			<td class="form_field">
				{if $element->sequenceName != ""}
					<img loading="lazy" src='{$controller->baseURL}file/id:{$element->sequence}/filename:{$element->sequenceName}' />
					<a href="{$element->URL}id:{$element->id}/file:sequence/action:deleteFile/" class="form_deletefile" >{translations name='zxpicture.deletefile'} {$element->sequenceName}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.sequence}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="sequence"}
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.inspired} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.inspired'}:
			</td>
			<td class="form_field">
				{if $element->inspiredName != ""}
					<img loading="lazy" src='{$controller->baseURL}image/type:adminImage/id:{$element->inspired}/filename:{$element->inspiredName}' />
					<a href="{$element->URL}id:{$element->id}/file:inspired/action:deleteFile/" class="form_deletefile">{translations name='zxpicture.deletefile'} {$element->inspiredName}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.inspired}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="inspired"}
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.inspired2} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.inspired2'}:
			</td>
			<td class="form_field">
				{if $element->inspired2Name != ""}
					<img loading="lazy" src='{$controller->baseURL}image/type:adminImage/id:{$element->inspired2}/filename:{$element->inspired2Name}' />
					<a href="{$element->URL}id:{$element->id}/file:inspired2/action:deleteFile/" class="form_deletefile">{translations name='zxpicture.deletefile'} {$element->inspired2Name}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.inspired2}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="inspired2"}
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.exeFile} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxpicture.exefile'}:
			</td>
			<td class="form_field">
				{if $element->exeFileName != ""}
					<a href="{$element->URL}id:{$element->id}/file:exeFile/action:deleteFile/" class="form_deletefile">{translations name='zxpicture.deletefile'} {$element->exeFileName}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.exeFile}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="exefile"}
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.description} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.description'}:
			</td>
			<td class="form_field">
				<textarea class='textarea_component' name="{$formNames.description}">{$formData.description}</textarea>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="description"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='field.rotation'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.rotation}" autocomplete='off'>
					<option value='0' {if 0 == $formData.rotation}selected="selected"{/if}>0</option>
					<option value='90' {if 90 == $formData.rotation}selected="selected"{/if}>90</option>
					<option value='180' {if 180 == $formData.rotation}selected="selected"{/if}>180</option>
					<option value='270' {if 270 == $formData.rotation}selected="selected"{/if}>270</option>
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="rotation"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxpicture.denyvoting'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyVoting}"{if $element->denyVoting} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="denyvoting"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxpicture.denycomments'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyComments}"{if $element->denyComments} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="denycomments"}
			</td>
		</tr>
		<tr {if $formErrors.image} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.image'}:
			</td>
			<td class="form_field">
				{if $element->originalName != ""}
					<img loading="lazy" src='{$element->getImageUrl()}' />
						<a href="{$element->URL}id:{$element->id}/file:image/action:deleteFile/" class="form_deletefile">{translations name='zxpicture.deletefile'} {$element->originalName}</a>
				{/if}
				<div>
					<input class="fileinput_placeholder" type="file" name="{$formNames.image}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="image"}
				</div>
			</td>
		</tr>
	</table>
	{include file=$theme->template('component.controls.tpl') action="publicReceive"}
</form>