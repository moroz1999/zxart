{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<div class="musicuploadform_topnotice">{translations name="musicuploadform.topnotice"}</div>
<form action="{$element->URL}" method="post" class="zxitem_form form_component" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.musicTitle} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.title'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.musicTitle}" name="{$formNames.musicTitle}" />
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="title"}
			</td>
		</tr>
		<tr {if $formErrors.author} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.author'}:
			</td>
			<td class="form_field">
				<select class="select_multiple zxitem_form_authors_select" multiple="multiple" name="{$formNames.author}[]" autocomplete='off'>
					{foreach from=$element->getAuthorsList() item=author}
						<option value='{$author->id}' selected="selected">
							{$author->getSearchTitle}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="author"}
			</td>
		</tr>
		<tr {if $formErrors.formatGroup} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.formatgroup'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.formatGroup}" >
					<option value=''> </option>
					<option value='ay' {if $formData.formatGroup=='ay' || !$formData.formatGroup}selected='selected'{/if}>AY/YM</option>
					<option value='beeper' {if $formData.formatGroup=='beeper'}selected='selected'{/if}>Beeper</option>
					<option value='digitalbeeper' {if $formData.compo=='digitalbeeper'}selected='selected'{/if}>Digital Beeper</option>
					<option value='beeperdigitalbeeper' {if $formData.compo=='beeperdigitalbeeper'}selected='selected'{/if}>Beeper + Digital Beeper</option>
					<option value='digitalay' {if $formData.formatGroup=='digitalay'}selected='selected'{/if}>Digital AY, Covox, SD</option>
					<option value='ts' {if $formData.formatGroup=='ts'}selected='selected'{/if}>Turbo Sound</option>
					<option value='fm' {if $formData.formatGroup=='fm'}selected='selected'{/if}>FM</option>
					<option value='tsfm' {if $formData.formatGroup=='tsfm'}selected='selected'{/if}>Turbo Sound FM</option>
					<option value='aybeeper' {if $formData.formatGroup=='aybeeper'}selected='selected'{/if}>AY/YM + Beeper</option>
					<option value='aydigitalay' {if $formData.formatGroup=='aydigitalay'}selected='selected'{/if}>AY/YM + Digital AY</option>
					<option value='aycovox' {if $formData.formatGroup=='aycovox'}selected='selected'{/if}>AY/YM + Covox</option>
					<option value='saa' {if $formData.formatGroup=='saa'}selected='selected'{/if}>SAA</option>
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="formatgroup"}
			</td>
		</tr>
		<tr {if $formErrors.chipType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.chiptype'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.chipType}" >
					<option value='' {if !$formData.chipType}selected='selected'{/if}>{translations name="zxmusic.chiptype_default"}</option>
					<option value='ay' {if $formData.chipType=='ay'}selected='selected'{/if}>AY</option>
					<option value='ym' {if $formData.chipType=='ym'}selected='selected'{/if}>YM</option>
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="chiptype"}
			</td>
		</tr>
		<tr {if $formErrors.channelsType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.channelstype'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.channelsType}" >
					<option value='' {if !$formData.channelsType}selected='selected'{/if}>{translations name="zxmusic.channelstype_default"}</option>
					{foreach $element->getChannelsTypes() as $type}
						<option value='{$type}' {if $type == $formData.channelsType}selected='selected'{/if}>{translations name="zxmusic.channelstype_{$type}"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="channelstype"}
			</td>
		</tr>
		<tr {if $formErrors.frequency} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.frequency'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.frequency}" >
					<option value='' {if !$formData.frequency}selected='selected'{/if}>{translations name="zxmusic.frequency_default"}</option>
					{foreach $element->getFrequencies() as $frequency}
						<option value='{$frequency}' {if $frequency == $formData.frequency}selected='selected'{/if}>{translations name="zxmusic.frequency_{$frequency}"}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.intFrequency} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.intFrequency'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.intFrequency}" >
					<option value='' {if !$formData.intFrequency}selected='selected'{/if}>{translations name="zxmusic.frequency_default"}</option>
					{foreach $element->getIntFrequencies() as $type}
						<option value='{$type}' {if $type == $formData.intFrequency}selected='selected'{/if}>{translations name="zxmusic.intfrequency_{$type|replace:'.':''}"}</option>
					{/foreach}
				</select>
			</td>
		</tr>		<tr {if $formErrors.party} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.party'}:
			</td>
			<td class="form_field">
				<select class="zxitem_form_party_select" name="{$formNames.party}" autocomplete='off'>
					{assign var="partyElement" value=$element->getPartyElement()}
					{if $partyElement}
						<option value='{$partyElement->id}' selected="selected">{$partyElement->title}</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="party"}
			</td>
		</tr>
		<tr {if $formErrors.partyplace} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.partyplace'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.partyplace}" name="{$formNames.partyplace}" />
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="partyplace"}
			</td>
		</tr>
		<tr {if $formErrors.compo} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.compo'}:
			</td>
			<td class="form_field">
				<select class="dropdown_placeholder" name="{$formNames.compo}" >
					<option value=''></option>
					{foreach $element->getCompoTypes() as $type}
						<option value='{$type}' {if $formData.compo===$type}selected='selected'{/if}>{translations name="musiccompo.compo_$type"}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="compo"}

			</td>
		</tr>
		<tr {if $formErrors.year} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.year'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.year}" name="{$formNames.year}" />
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="year"}
			</td>
		</tr>
		<tr {if $formErrors.game} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.release'}:
			</td>
			<td class="form_field">
				<select class="zxitem_form_prodrelease_select" name="{$formNames.game}" autocomplete='off'>
					{foreach from=$formData.game item=gameId}
						<option value="{$gameId}" selected="selected">{$gameId}</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="game"}
			</td>
		</tr>
		<tr {if $formErrors.tagsText} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.tagstext'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.tagsText}" name="{$formNames.tagsText}" />
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="tagstext"}
			</td>
		</tr>
		<tr {if $formErrors.description} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.description'}:
			</td>
			<td class="form_field">
				<textarea class='textarea_component' name="{$formNames.description}">{$formData.description}</textarea>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="description"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxmusic.denyvoting'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyVoting}"{if $element->denyVoting} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="denyvoting"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxmusic.denycomments'}:
			</td>
			<td class="form_field">
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyComments}"{if $element->denyComments} checked="checked"{/if}/>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="denycomments"}
			</td>
		</tr>
		<tr {if $formErrors.music} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.playablefile'}:
			</td>
			<td class="form_field">
				<input class="fileinput_placeholder" type="file" name="{$formNames.music}[]" multiple="multiple"/>
				{include file=$theme->template('component.form_help.tpl') structureType="zxMusic" name="playablefile"}
			</td>
		</tr>
	</table>
	{include file=$theme->template('component.controls.tpl') action="batchUpload"}
</form>