{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxrelease_form zxitem_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr{if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxRelease.title'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>

		<tr {if $formErrors.zxProd} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.zxProd'}:
			</td>
			<td class="form_field">
				<select class="zxitem_form_prod_select" name="{$formNames.zxProd}" autocomplete='off'>
					<option value=''></option>
					{foreach $formData.zxProd as $prod}
						<option value='{$prod->id}' selected="selected">
							{$prod->title}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="zxProd"}
			</td>
		</tr>
		<tr {if $formErrors.year} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxRelease.year'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.year}" name="{$formNames.year}"/>
				{include file=$theme->template('component.form_help.tpl') structureType='zxRelease' name="year"}
			</td>
		</tr>
		<tr {if $formErrors.version} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxRelease.version'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.version}" name="{$formNames.version}"/>
				{include file=$theme->template('component.form_help.tpl') structureType='zxRelease' name="version"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxRelease.denyvoting'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyVoting}"{if $element->denyVoting} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="denyvoting"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxRelease.denycomments'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyComments}"{if $element->denyComments} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="denycomments"}
			</td>
		</tr>
		<tr {if $formErrors.file} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxRelease.file'}:
			</td>
			<td class="form_field">
				{if $element->fileName != ""}
					<a href="{$element->URL}id:{$element->file}/file:file/action:deleteFile/" class="form_deletefile">{translations name='zxpicture.deletefile'} {$element->fileName}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.file}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="file"}
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.releaseType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxRelease.releaseType'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.releaseType}" >
					{foreach $element->getReleaseTypes() as $type}
						<option value='{$type}' {if $formData.releaseType==$type}selected='selected'{/if}>{translations name="zxRelease.type_{$type}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="releaseType"}
			</td>
		</tr>
		<tr {if $formErrors.releaseFormat} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxRelease.releaseFormat'}:
			</td>
			<td class="form_field">
				<select class="select_multiple" name="{$formNames.releaseFormat}[]" multiple="multiple" >
					{foreach $element->getReleaseFormats() as $format}
						<option value='{$format}' {if in_array($format, $formData.releaseFormat)}selected='selected'{/if}>{translations name="zxRelease.filetype_{$format}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="releaseFormat"}
			</td>
		</tr>
		<tr {if $formErrors.language} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxRelease.language'}:
			</td>
			<td class="form_field">
				<select class="select_multiple" name="{$formNames.language}[]" multiple="multiple" >
					{foreach from=$element->getLanguageCodes() item=languageItem}
						<option value='{$languageItem}' {if in_array($languageItem, $formData.language)}selected='selected'{/if}>{translations name="language.item_{$languageItem}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="language"}
			</td>
		</tr>
		<tr {if $formErrors.publishers} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.publishers'}:
			</td>
			<td class="form_field">
				<select class="select_multiple zxitem_form_groups_select" multiple="multiple" name="{$formNames.publishers}[]" autocomplete='off'>
					<option value=''></option>
					{foreach $formData.publishers as $publisher}
						<option value='{$publisher->id}' selected="selected">
							{$publisher->title}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="publishers"}
			</td>
		</tr>
		<tr {if $formErrors.hardwareRequired} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxRelease.hardwareRequired'}:
			</td>
			<td class="form_field">
				<select class="select_multiple" name="{$formNames.hardwareRequired}[]" multiple="multiple" >
					{foreach from=$element->getHardwareList() key=groupName item=hardwareList}
						<optgroup label="{translations name="hardware.group_{$groupName}"}">
							{foreach $hardwareList as $hardwareItem}
								<option value='{$hardwareItem}' {if in_array($hardwareItem, $formData.hardwareRequired)}selected='selected'{/if}>{translations name="hardware.item_{$hardwareItem}"}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="hardwareRequired"}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3>{translations name='zxprod.authors'}:</h3>
			</td>
		</tr>
		{include file=$theme->template('component.form.authors.tpl') element=$element displayDate=false type='release' translationsGroup='zxProd'}
		<tr>
			<td colspan="2">
				<h3>{translations name='zxprod.screenshots'}:</h3>
			</td>
		</tr>
		{include file=$theme->template('component.form.files.tpl') element=$element propertyName='screenshotsSelector' imagePreset='prodImage'}

		<tr>
			<td colspan="2">
				<h3>{translations name='zxrelease.inlays'}:</h3>
			</td>
		</tr>
		{include file=$theme->template('component.form.files.tpl') element=$element propertyName='inlayFilesSelector' imagePreset='prodImage'}

		<tr>
			<td colspan="2">
				<h3>{translations name='zxrelease.ads'}</h3>
			</td>
		</tr>
		{include file=$theme->template('component.form.files.tpl') element=$element propertyName='adFilesSelector' imagePreset='prodImage'}

		<tr>
			<td colspan="2">
				<h3>{translations name='zxrelease.instructions'}</h3>
			</td>
		</tr>
		{include file=$theme->template('component.form.files.tpl') element=$element propertyName='infoFilesSelector' imagePreset='prodImage'}

	</table>
	{if $element->hasActualStructureInfo()}
		{include file=$theme->template('component.controls.tpl') action='publicReceive'}
	{else}
		{include file=$theme->template('component.controls.tpl') action='publicAdd'}
	{/if}
</form>
