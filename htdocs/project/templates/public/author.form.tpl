{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="author_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr{if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.nickname'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>
		{foreach from=$formData.realName key=languageId item=realName}
		<tr {if $formErrors.realName.$languageId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.realname'} ({$languageNames.$languageId}):
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$realName}" name="{$formNames.realName.$languageId}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="realname"}
			</td>
		</tr>
		{/foreach}
		<tr {if $formErrors.country} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.country'}:
			</td>
			<td class="form_field">
				<select class="author_form_country_select" name="{$formNames.country}" autocomplete='off'>
					{assign var="countryElement" value=$element->getCountryElement()}
					{if $countryElement}
						<option value='{$countryElement->id}' selected="selected">
							{$countryElement->title}
						</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="country"}
			</td>
		</tr>
		<tr {if $formErrors.city} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.city'}:
			</td>
			<td class="form_field">
				<select class="author_form_city_select" name="{$formNames.city}" autocomplete='off'>
					{assign var="cityElement" value=$element->getCityElement()}
					{if $cityElement}
						<option value='{$cityElement->id}' selected="selected">
							{$cityElement->title}
						</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="city"}
			</td>
		</tr>
		<tr {if $formErrors.wikiLink} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.wikilink'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.wikiLink}" name="{$formNames.wikiLink}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="wikilink"}
			</td>
		</tr>

		<tr {if $formErrors.zxTunesId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.zxtunesid'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.zxTunesId}" name="{$formNames.zxTunesId}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="zxtunesid"}
			</td>
		</tr>

		<tr {if $formErrors.image} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.image'}:
			</td>
			<td class="form_field">
				{if $element->originalName != ""}
					<img loading="lazy" src='{$controller->baseURL}image/type:adminImage/id:{$element->image}/filename:{$element->originalName}' />
					<br />
					<a href="{$element->URL}id:{$element->id}/action:deleteFile/file:image/" >{translations name='author.deleteimage'}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.image}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="image"}
				{/if}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.denyvoting'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyVoting}"{if $element->denyVoting} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="denyvoting"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.denycomments'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyComments}"{if $element->denyComments} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="denycomments"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.deny3a'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.deny3a}"{if $element->deny3a} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="deny3a"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.displayinmusic'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.displayInMusic}"{if $element->displayInMusic} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="displayinmusic"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.displayingraphics'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.displayInGraphics}"{if $element->displayInGraphics} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="displayingraphics"}
			</td>
		</tr>
		<tr {if $formErrors.chipType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.chiptype'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.chipType}" >
					{foreach $element->getChipTypes() as $type}
						<option value='{$type}' {if $type == $formData.chipType}selected='selected'{/if}>{translations name="zxmusic.chiptype_{$type}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="chiptype"}
			</td>
		</tr>
		<tr {if $formErrors.channelsType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.channelstype'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.channelsType}" >
					{foreach $element->getChannelsTypes() as $type}
						<option value='{$type}' {if $type == $formData.channelsType}selected='selected'{/if}>{translations name="zxmusic.channelstype_{$type}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="channelstype"}
			</td>
		</tr>
		<tr {if $formErrors.frequency} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.frequency'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.frequency}" >
					{foreach $element->getFrequencies() as $frequency}
						<option value='{$frequency}' {if $frequency == $formData.frequency || !$formData.frequency && $frequency == 1750000}selected='selected'{/if}>{translations name="zxmusic.frequency_{$frequency}"}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.intFrequency} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.intFrequency'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.intFrequency}" >
					{foreach $element->getIntFrequencies() as $type}
						<option value='{$type}' {if $type == $formData.intFrequency}selected='selected'{/if}>{translations name="zxmusic.intfrequency_{$type|replace:'.':''}"}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.palette} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.palette'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.palette}" >
					<option value="">{translations name="author.palette_auto"}</option>
					{foreach $element->getPaletteTypes() as $type}
						<option value='{$type}' {if $type == $formData.palette}selected='selected'{/if}>{translations name="zxpicture.palette_{$type}"}</option>
					{/foreach}
				</select>
			</td>
		</tr>
	</table>
	{if $element->hasActualStructureInfo()}
		{include file=$theme->template('component.controls.tpl') action='publicReceive'}
	{else}
		{include file=$theme->template('component.controls.tpl') action='publicAdd'}
	{/if}
</form>
