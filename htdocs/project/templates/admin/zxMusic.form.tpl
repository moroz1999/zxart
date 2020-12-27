{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.name'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
			</td>
		</tr>
		<tr {if $formErrors.party} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.party'}:
			</td>
			<td colspan='2'>
				<select class="zxitem_form_party_select" name="{$formNames.party}" autocomplete='off'>
					{assign var="partyElement" value=$element->getPartyElement()}
					{if $partyElement}
						<option value='{$partyElement->id}' selected="selected">
							{$partyElement->title}
						</option>
					{/if}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.partyplace} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.partyplace'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.partyplace}" name="{$formNames.partyplace}" />
			</td>
		</tr>
		<tr {if $formErrors.compo} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.compo'}:
			</td>
			<td>
				<select class="dropdown_placeholder" name="{$formNames.compo}">
					<option value='standard' {if $formData.compo=='standard'}selected='selected'{/if}>General compo (AY/Beeper/TS/...)</option>
					<option value='ay' {if $formData.compo=='ay'}selected='selected'{/if}>Ay</option>
					<option value='beeper' {if $formData.compo=='beeper'}selected='selected'{/if}>Beeper</option>
					<option value='copyay' {if $formData.compo=='copyay'}selected='selected'{/if}>Ay (Copy)</option>
					<option value='nocopyay' {if $formData.compo=='nocopyay'}selected='selected'{/if}>Ay (No-Copy)</option>
					<option value='realtime' {if $formData.compo=='realtime'}selected='selected'{/if}>Realtime</option>
					<option value='realtimec' {if $formData.compo=='realtimec'}selected='selected'{/if}>Realtime cover</option>
					<option value='realtimeay' {if $formData.compo=='realtimeay'}selected='selected'{/if}>Realtime AY</option>
					<option value='realtimebeeper' {if $formData.compo=='realtimebeeper'}selected='selected'{/if}>Realtime Beeper</option>
					<option value='out' {if $formData.compo=='out'}selected='selected'{/if}>Out of compo</option>
					<option value='wild' {if $formData.compo=='wild'}selected='selected'{/if}>Wild</option>
					<option value='experimental' {if $formData.compo=='experimental'}selected='selected'{/if}>Experimental Sound</option>
					<option value='oldschool' {if $formData.compo=='oldschool'}selected='selected'{/if}>Oldschool Music</option>
					<option value='mainstream' {if $formData.compo=='mainstream'}selected='selected'{/if}>Mainstream Music</option>
					<option value='progressive' {if $formData.compo=='progressive'}selected='selected'{/if}>Progressive Music</option>
					<option value='tsfm' {if $formData.compo=='tsfm'}selected='selected'{/if}>TurboFM Music Compo</option>
					<option value='related' {if $formData.compo=='related'}selected='selected'{/if}>Party-related works</option>
				</select>
			</td>
		</tr>
		<tr {if $formErrors.author} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.author'}:
			</td>
			<td colspan='2'>
				<select class="select_multiple zxitem_form_authors_select" multiple="multiple" name="{$formNames.author}[]" autocomplete='off'>
					<option value=''></option>
					{foreach from=$element->getAuthorsList() item=author}
						<option value='{$author->id}' selected="selected">
							{$author->title}
						</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.game} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.release'}:
			</td>
			<td colspan='2'>
				<select class="zxitem_form_prodrelease_select" name="{$formNames.game}" autocomplete='off'>
					{assign var="gameElement" value=$element->getGameElement()}
					{if $gameElement}
						<option value='{$gameElement->id}' selected="selected">
							{$gameElement->title}
						</option>
					{/if}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.year} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.year'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.year}" name="{$formNames.year}" />
			</td>
		</tr>
		<tr {if $formErrors.tagsText} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.tagstext'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.tagsText}" name="{$formNames.tagsText}" />
			</td>
		</tr>
		<tr {if $formErrors.formatGroup} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.formatgroup'}:
			</td>
			<td>
				<select class="dropdown_placeholder" name="{$formNames.formatGroup}">
					<option value=''></option>
					<option value='ay' {if $formData.formatGroup=='ay' || !$formData.formatGroup}selected='selected'{/if}>AY/YM</option>
					<option value='beeper' {if $formData.formatGroup=='beeper'}selected='selected'{/if}>Beeper</option>
					<option value='digitalbeeper' {if $formData.compo=='digitalbeeper'}selected='selected'{/if}>Digital Beeper</option>
					<option value='beeperdigitalbeeper' {if $formData.compo=='beeperdigitalbeeper'}selected='selected'{/if}>Beeper + Digital Beeper</option>
					<option value='digitalay' {if $formData.formatGroup=='digitalay'}selected='selected'{/if}>Digital AY, Covox, SD</option>
					<option value='ts' {if $formData.formatGroup=='ts'}selected='selected'{/if}>Turbo Sound</option>
					<option value='tsfm' {if $formData.formatGroup=='tsfm'}selected='selected'{/if}>Turbo Sound FM</option>
					<option value='fm' {if $formData.formatGroup=='fm'}selected='selected'{/if}>FM</option>
					<option value='aybeeper' {if $formData.formatGroup=='aybeeper'}selected='selected'{/if}>AY/YM + Beeper</option>
					<option value='aydigitalay' {if $formData.formatGroup=='aydigitalay'}selected='selected'{/if}>AY/YM + Digital AY</option>
					<option value='aycovox' {if $formData.formatGroup=='aycovox'}selected='selected'{/if}>AY/YM + Covox</option>
					<option value='saa' {if $formData.formatGroup=='saa'}selected='selected'{/if}>SAA</option>
				</select>
			</td>
		</tr>
		<tr {if $formErrors.chipType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.chiptype'}:
			</td>
			<td>
				<select class="dropdown_placeholder" name="{$formNames.chipType}">
					<option value='' {if !$formData.chipType}selected='selected'{/if}>{translations name="zxmusic.chiptype_default"}</option>
					<option value='ay' {if $formData.chipType=='ay'}selected='selected'{/if}>AY</option>
					<option value='ym' {if $formData.chipType=='ym'}selected='selected'{/if}>YM</option>
				</select>
			</td>
		</tr>
		<tr {if $formErrors.channelsType} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.channelstype'}:
			</td>
			<td>
				<select class="dropdown_placeholder" name="{$formNames.channelsType}">
					<option value='' {if !$formData.channelsType}selected='selected'{/if}>{translations name="zxmusic.channelstype_default"}</option>
					<option value='ABC' {if $formData.channelsType=='ABC'}selected='selected'{/if}>ABC</option>
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
				{translations name='zxmusic.frequency'}:
			</td>
			<td>
				<select class="dropdown_placeholder" name="{$formNames.frequency}" >
					<option value='' {if !$formData.frequency}selected='selected'{/if}>{translations name="zxmusic.frequency_default"}</option>
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
				{translations name='zxmusic.intFrequency'}:
			</td>
			<td>
				<select class="dropdown_placeholder" name="{$formNames.intFrequency}" >
					<option value='' {if !$formData.intFrequency}selected='selected'{/if}>{translations name="zxmusic.frequency_default"}</option>
					<option value='48.828125' {if $formData.intFrequency=='48.828125'}selected='selected'{/if}>48.828125 Hz (Pentagon) </option>
					<option value='50' {if $formData.intFrequency=='50'}selected='selected'{/if}>50 Hz (Standard ZX) </option>
					<option value='60' {if $formData.intFrequency=='60'}selected='selected'{/if}>60 Hz (MSX) </option>
					<option value='100' {if $formData.intFrequency=='100'}selected='selected'{/if}>100 Hz (Enhanced) </option>
					<option value='200' {if $formData.intFrequency=='200'}selected='selected'{/if}>200 Hz (Atari) </option>
					<option value='1000' {if $formData.intFrequency=='1000'}selected='selected'{/if}>1000 Hz (Enhanced) </option>
				</select>
			</td>
		</tr>		<tr {if $formErrors.description} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.description'}:
			</td>
			<td>
				<textarea class='textarea_component' name="{$formNames.description}">{$formData.description}</textarea>
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxmusic.denyvoting'}:
			</td>
			<td>
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyVoting}"{if $element->denyVoting} checked="checked"{/if}/>
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxmusic.denycomments'}:
			</td>
			<td>
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyComments}"{if $element->denyComments} checked="checked"{/if}/>
			</td>
		</tr>

		<tr {if $formErrors.embedCode} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxmusic.embedcode'}:
			</td>
			<td>
				<textarea class='textarea_component' name="{$formNames.embedCode}">{$formData.embedCode}</textarea>
			</td>
		</tr>
		<tr {if $formErrors.file} class="form_error"{/if}>
			<td class="form_label">
				Playable file:
			</td>
			<td>
				{if $element->fileName != ""}
					<a href="{$element->URL}id:{$element->id}/file:file/action:deleteFile/">{translations name='label.deletefile'} {$element->fileName}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.file}" />
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.trackerFile} class="form_error"{/if}>
			<td class="form_label">
				Original non-playable file (if required):
			</td>
			<td>
				{if $element->trackerFileName != ""}
					<a href="{$element->URL}id:{$element->id}/file:trackerFile/action:deleteFile/">{translations name='label.deletefile'} {$element->trackerFileName}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.trackerFile}" />
				{/if}
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>
{if $element->getCommentsAmount()>0}
	<table>
		{foreach from=$element->getCommentsList() item=comment}
			<tr>
				<td>
					{$comment->content}
				</td>
				<td>
					<a href="{$comment->URL}id:{$comment->id}/action:delete">{translations name='label.delete'}</a>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}