{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.prodTitle} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxProd.title'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.prodTitle}" name="{$formNames.prodTitle}" />
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="title"}
			</td>
		</tr>
		<tr {if $formErrors.prodAltTitle} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxProd.altTitle'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.prodAltTitle}" name="{$formNames.prodAltTitle}" />
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="altTitle"}
			</td>
		</tr>
		<tr {if $formErrors.externalLink} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.externalLink'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.externalLink}" name="{$formNames.externalLink}" />
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="externalLink"}
			</td>
		</tr>
		<tr {if $formErrors.categories} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.categories'}:
			</td>
			<td class="form_field">
				<select class="zxitem_form_categories_select select_multiple" name="{$formNames.categories}[]" autocomplete='off' multiple="multiple">
					{foreach $element->getCategoriesSelectorInfo() as $categoryInfo}
						<option value='{$categoryInfo.id}' {if $categoryInfo.selected}selected="selected"{/if} >{for $level=0 to $categoryInfo.level-4}&nbsp;{/for}{$categoryInfo.title}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="categories"}
			</td>
		</tr>
		<tr {if $formErrors.language} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.language'}:
			</td>
			<td class="form_field">
				<select class="select_multiple" name="{$formNames.language}[]" multiple="multiple">
					{foreach from=$element->getLanguageCodes() item=languageItem}
						<option value='{$languageItem}' {if $formData.language && in_array($languageItem, $formData.language)}selected='selected'{/if}>{translations name="language.item_{$languageItem}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="language"}
			</td>
		</tr>
		<tr {if $formErrors.legalStatus} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxProd.legalStatus'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.legalStatus}">
					<option value='unknown' {if $formData.legalStatus=='unknown'}selected='selected'{/if}>{translations name='legalstatus.unknown'}</option>
					<option value='allowed' {if $formData.legalStatus=='allowed'}selected='selected'{/if}>{translations name='legalstatus.allowed'}</option>
					<option value='allowedzxart' {if $formData.legalStatus=='allowedzxart'}selected='selected'{/if}>{translations name='legalstatus.allowedzxart'}</option>
					<option value='forbidden' {if $formData.legalStatus=='forbidden'}selected='selected'{/if}>{translations name='legalstatus.forbidden'}</option>
					<option value='forbiddenzxart' {if $formData.legalStatus=='forbiddenzxart'}selected='selected'{/if}>{translations name='legalstatus.forbiddenzxart'}</option>
					<option value='insales' {if $formData.legalStatus=='insales'}selected='selected'{/if}>{translations name='legalstatus.insales'}</option>
					<option value='mia' {if $formData.legalStatus=='mia'}selected='selected'{/if}>{translations name='legalstatus.mia'}</option>
					<option value='unreleased' {if $formData.legalStatus=='unreleased'}selected='selected'{/if}>{translations name='legalstatus.unreleased'}</option>
					<option value='recovered' {if $formData.legalStatus=='recovered'}selected='selected'{/if}>{translations name='legalstatus.recovered'}</option>
					<option value='donationware' {if $formData.legalStatus=='donationware'}selected='selected'{/if}>{translations name='legalstatus.donationware'}</option>
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="legalStatus"}
			</td>
		</tr>
		<tr {if $formErrors.groups} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.groups'}:
			</td>
			<td class="form_field">
				<select class="select_multiple zxitem_form_groups_select" multiple="multiple" name="{$formNames.groups}[]" autocomplete='off'>
					<option value=''></option>
					{foreach from=$formData.groups item=group}
						<option value='{$group->id}' selected="selected">
							{$group->title}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="groups"}
			</td>
		</tr>
		<tr {if $formErrors.publishers} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.publishers'}:
			</td>
			<td class="form_field">
				<select class="select_multiple zxitem_form_publishers_select" multiple="multiple" name="{$formNames.publishers}[]" autocomplete='off'>
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
		<tr {if $formErrors.party} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.party'}:
			</td>
			<td class="form_field">
				<select class="zxitem_form_party_select" name="{$formNames.party}" autocomplete='off'>
					{assign var="partyElement" value=$element->getPartyElement()}
					{if $partyElement}
						<option value='{$partyElement->id}' selected="selected">{$partyElement->title}</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="party"}
			</td>
		</tr>
		<tr {if $formErrors.partyplace} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.partyplace'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.partyplace}" name="{$formNames.partyplace}" />
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="partyplace"}
			</td>
		</tr>
		<tr {if $formErrors.compo} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.compo'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.compo}">
					<option value=""></option>
					{foreach $element->getCompoTypes() as $compoType}
						<option value='{$compoType}' {if $formData.compo=={$compoType}}selected='selected'{/if}>{translations name="party.compo_{$compoType}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="compo"}
			</td>
		</tr>
		<tr {if $formErrors.year} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.year'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.year}" name="{$formNames.year}" />
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="year"}
			</td>
		</tr>
		<tr {if $formErrors.year} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.youtubeId'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.youtubeId}" name="{$formNames.youtubeId}" />
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="youtubeId"}
			</td>
		</tr>
		<tr {if $formErrors.description} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.description'}:
			</td>
			<td class="form_field">
				<textarea class='textarea_component' name="{$formNames.description}">{$formData.description}</textarea>
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="description"}
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
		<tr>
			<td class="form_label">
				{translations name='zxprod.denyvoting'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyVoting}"{if $element->denyVoting} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="denyvoting"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxprod.denycomments'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyComments}"{if $element->denyComments} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="denycomments"}
			</td>
		</tr>
		<tr {if $formErrors.file} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxProd.file'}:
			</td>
			<td class="form_field">
				<input class="fileinput_placeholder" type="file" name="{$formNames.file}[]" multiple="multiple"/>
				{include file=$theme->template('component.form_help.tpl') structureType='zxProd' name="file"}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3>{translations name='zxprod.authors'}:</h3>
			</td>
		</tr>
	    {include file=$theme->template('component.form.authors.tpl') element=$element displayDate=false type='prod' translationsGroup='zxProd'}

		<tr {if $formErrors.connectedFile} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.screenshots'}
			</td>
			<td class="form_field">
				<input class="fileinput_placeholder" type="file" name="{$formNames.connectedFile}[]" multiple="multiple" />
			</td>
		</tr>
		<tr {if $formErrors.mapFilesSelector} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxprod.maps'}
			</td>
			<td class="form_field">
				<input class="fileinput_placeholder" type="file" name="{$formNames.mapFilesSelector}[]" multiple="multiple" />
			</td>
		</tr>
	</table>
	{include file=$theme->template('component.controls.tpl') action="batchUpload"}
</form>
