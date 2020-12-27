{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="author_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr{if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.nick'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
			</td>
		</tr>
		{foreach from=$formData.realName key=languageId item=realName}
		<tr {if $formErrors.realName.$languageId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.name'} ({$languageNames.$languageId}):
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$realName}" name="{$formNames.realName.$languageId}" />
			</td>
		</tr>
		{/foreach}
		<tr {if $formErrors.country} class="form_error"{/if}>
				<td class="form_label">
					{translations name='field.country'}:
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
				</td>
			</tr>
		<tr {if $formErrors.city} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.city'}:
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
			</td>
		</tr>
		<tr {if $formErrors.wikiLink} class="form_error"{/if}>
			<td class="form_label">
				Wiki link:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.wikiLink}" name="{$formNames.wikiLink}" />
			</td>
		</tr>
		<tr {if $formErrors.zxTunesId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.zxtunesid'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.zxTunesId}" name="{$formNames.zxTunesId}" />
			</td>
		</tr>
		<tr {if $formErrors.image} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.image'}:
			</td>
			<td class="form_field">
				{if $element->originalName != ""}
					<img src='{$controller->baseURL}image/type:adminImage/id:{$element->image}/filename:{$element->originalName}' />
					<br />
					<a href="{$element->URL}id:{$element->id}/action:deleteFile/file:image/" >{translations name='label.deleteimage'}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.image}" />
				{/if}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.denyvoting'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyVoting}"{if $element->denyVoting} checked="checked"{/if}/>
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.denycomments'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyComments}"{if $element->denyComments} checked="checked"{/if}/>
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.displayinmusic'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.displayInMusic}"{if $element->displayInMusic} checked="checked"{/if}/>
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='author.displayingraphics'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.displayInGraphics}"{if $element->displayInGraphics} checked="checked"{/if}/>
			</td>
		</tr>
		<tr {if $formErrors.chipType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.chiptype'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.chipType}" >
					<option value='ay' {if $formData.chipType=='ay'}selected='selected'{/if}>AY</option>
					<option value='ym' {if !$formData.chipType || $formData.chipType=='ym'}selected='selected'{/if}>YM</option>
				</select>
			</td>
		</tr>
		<tr {if $formErrors.channelsType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.channelstype'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.channelsType}" >
					<option value='ABC' {if !$formData.channelsType || $formData.channelsType=='ABC'}selected='selected'{/if}>ABC</option>
					<option value='ACB' {if $formData.channelsType=='ACB'}selected='selected'{/if}>ACB</option>
					<option value='BAC' {if $formData.channelsType=='BAC'}selected='selected'{/if}>BAC</option>
					<option value='BCA' {if $formData.channelsType=='BCA'}selected='selected'{/if}>BCA</option>
					<option value='CBA' {if $formData.channelsType=='CBA'}selected='selected'{/if}>CBA</option>
					<option value='CAB' {if $formData.channelsType=='CAB'}selected='selected'{/if}>CAB</option>
					<option value='mono' {if $formData.channelsType=='mono'}selected='selected'{/if}>mono</option>
				</select>
			</td>
		</tr>
		<tr {if $formErrors.frequency} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.frequency'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.frequency}" >
					<option value='1750000' {if $formData.frequency=='1750000'}selected='selected'{/if}>1.75 MHz (Pentagon) </option>
					<option value='1770000' {if $formData.frequency=='1770000'}selected='selected'{/if}>1.77 MHz (Standard ZX) </option>
					<option value='1773400' {if $formData.frequency=='1773400'}selected='selected'{/if}>1.7734 MHz (Profi) </option>
					<option value='1789770' {if $formData.frequency=='1789770'}selected='selected'{/if}>1.789 MHz (Melodik/Didaktik) </option>
					<option value='2000000' {if $formData.frequency=='2000000'}selected='selected'{/if}>2.00 MHz (Atari) </option>
					<option value='3500000' {if $formData.frequency=='3500000'}selected='selected'{/if}>3.50 MHz (Enhanced) </option>
				</select>
			</td>
		</tr>
		<tr {if $formErrors.intFrequency} class="form_error"{/if}>
			<td class="form_label">
				{translations name='author.intFrequency'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.intFrequency}" >
					<option value='48.828125' {if $formData.intFrequency=='48.828125'}selected='selected'{/if}>48.828125 Hz (Pentagon) </option>
					<option value='50' {if $formData.intFrequency=='50'}selected='selected'{/if}>50 Hz (Standard ZX) </option>
					<option value='60' {if $formData.intFrequency=='60'}selected='selected'{/if}>60 Hz (MSX) </option>
					<option value='100' {if $formData.intFrequency=='100'}selected='selected'{/if}>100 Hz (Enhanced) </option>
					<option value='200' {if $formData.intFrequency=='200'}selected='selected'{/if}>200 Hz (Atari) </option>
					<option value='1000' {if $formData.intFrequency=='1000'}selected='selected'{/if}>1000 Hz (Enhanced) </option>
				</select>
			</td>
		</tr>
		<tr class="{if $formErrors.joinAsAlias} form_error{/if}">
			<td class="form_label">
				{translations name='author.joinauthor'}:
			</td>
			<td class="form_field">
				<select class="author_form_join_select" name="{$formNames.joinAsAlias}" autocomplete='off'></select>
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>
