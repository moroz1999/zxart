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
				<select class="dropdown_placeholder" name="{$formNames.compo}" >
					{foreach $element->getCompoTypes() as $compoType}
						<option value='{$compoType}' {if $compoType == $formData.compo}selected='selected'{/if}>{translations name="zxPicture.compo_{$compoType}"}</option>
					{/foreach}
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
				{translations name='zxpicture.release'}:
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
		<tr {if $formErrors.border} class="form_error"{/if}>
			<td class="form_label">
				Border:
			</td>
			<td>
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
			</td>
		</tr>
		<tr {if $formErrors.type} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.type'}:
			</td>
			<td>
				<select class="dropdown_placeholder" name="{$formNames.type}" >
					<option value='standard' {if $formData.type=='standard'}selected='selected'{/if}>Standard</option>
					<option value='gigascreen' {if $formData.type=='gigascreen'}selected='selected'{/if}>Gigascreen (img)</option>
					<option value='monochrome' {if $formData.type=='monochrome'}selected='selected'{/if}>Monochrome (6144)</option>
					<option value='flash' {if $formData.type=='flash'}selected='selected'{/if}>Flash</option>
					<option value='tricolor' {if $formData.type=='tricolor'}selected='selected'{/if}>Tricolor RGB(.+)</option>
					<option value='mg1' {if $formData.type=='mg1'}selected='selected'{/if}>Multiartist (.mg1)</option>
					<option value='mg2' {if $formData.type=='mg2'}selected='selected'{/if}>Multiartist (.mg2)</option>
					<option value='mg4' {if $formData.type=='mg4'}selected='selected'{/if}>Multiartist (.mg4)</option>
					<option value='mg8' {if $formData.type=='mg8'}selected='selected'{/if}>Multiartist (.mg8)</option>
					<option value='mlt' {if $formData.type=='mlt'}selected='selected'{/if}>Multicolor (.mlt 8*1)</option>
					<option value='mc' {if $formData.type=='mc'}selected='selected'{/if}>Multicolor (.mc 8*1)</option>
					<option value='multicolor' {if $formData.type=='multicolor'}selected='selected'{/if}>Multicolor (8*2)</option>
					<option value='multicolor4' {if $formData.type=='multicolor4'}selected='selected'{/if}>Multicolor (8*4)</option>
					<option value='attributes' {if $formData.type=='attributes'}selected='selected'{/if}>Attributes (768)</option>
					<option value='lowresgs' {if $formData.type=='lowresgs'}selected='selected'{/if}>Lowres Gigascreen (1628)</option>
					<option value='stellar' {if $formData.type=='stellar'}selected='selected'{/if}>Stellar Mode (lowres multicolor+gigascreen 64*48)</option>
					<option value='chr$' {if $formData.type=='chr$'}selected='selected'{/if}>CHR$ (ch$)</option>
					<option value='timex81' {if $formData.type=='timex81'}selected='selected'{/if}>timex 8*1 (12288)</option>
					<option value='timexhr' {if $formData.type=='timexhr'}selected='selected'{/if}>Timex hi-res 512*192 (12289)</option>
					<option value='timexhrg' {if $formData.type=='timexhrg'}selected='selected'{/if}>Timex hi-res Gigascreen 512*192 (24578)</option>
					<option value='sam4' {if $formData.type=='sam4'}selected='selected'{/if}>Sam Coupe mode 4 (.ss4)</option>
					<option value='bsc' {if $formData.type=='bsc'}selected='selected'{/if}>BSC (11136)</option>
					<option value='bsp' {if $formData.type=='bsp'}selected='selected'{/if}>BSP</option>
					<option value='bmc4' {if $formData.type=='bmc4'}selected='selected'{/if}>BMC4 (11904)</option>
					<option value='ulaplus' {if $formData.type=='ulaplus'}selected='selected'{/if}>ULA+ (6976)</option>
					<option value='zxevo' {if $formData.type=='zxevo'}selected='selected'{/if}>ZX Evolution (BMP)</option>
					<option value='sxg' {if $formData.type=='sxg'}selected='selected'{/if}>ZX Evolution (SXG)</option>
					<option value='nxi' {if $formData.type=='nxi'}selected='selected'{/if}>ZX Spectrum Next (nxi)</option>
				</select>
			</td>
		</tr>
		<tr {if $formErrors.sequence} class="form_error"{/if}>
			<td class="form_label">
				WIP Sequence:
			</td>
			<td>
				{if $element->sequenceName != ""}
					<img src='{$controller->baseURL}file/id:{$element->sequence}/filename:{$element->sequenceName}' />
					<br/>
					<input class="fileinput_placeholder" type="file" name="{$formNames.sequence}" />
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.sequence}" />
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.inspired} class="form_error"{/if}>
			<td class="form_label">
				Inspired by:
			</td>
			<td>
				{if $element->inspiredName != ""}
					<img src='{$controller->baseURL}image/type:adminImage/id:{$element->inspired}/filename:{$element->inspiredName}' />
					<input class="fileinput_placeholder" type="file" name="{$formNames.inspired}" />
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.inspired}" />
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.inspired2} class="form_error"{/if}>
			<td class="form_label">
				Inspired also by:
			</td>
			<td>
				{if $element->inspired2Name != ""}
					<img src='{$controller->baseURL}image/type:adminImage/id:{$element->inspired2}/filename:{$element->inspired2Name}' />
					<input class="fileinput_placeholder" type="file" name="{$formNames.inspired2}" />
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.inspired2}" />
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.exeFile} class="form_error"{/if}>
			<td class="form_label">
				ZX Spectrum executable file (if needed):
			</td>
			<td>
				{if $element->exeFileName != ""}
					<a href="{$element->URL}id:{$element->id}/file:exeFile/action:deleteFile/" >{translations name='label.deletefile'} {$element->exeFileName}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.exeFile}" />
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.description} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.description'}:
			</td>
			<td>
				<textarea class='textarea_component' name="{$formNames.description}">{$formData.description}</textarea>
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='field.rotation'}:
			</td>
			<td>
				<select class="dropdown_placeholder" name="{$formNames.rotation}" autocomplete='off'>
					<option value='0' {if 0 == $formData.rotation}selected="selected"{/if}>0</option>
					<option value='90' {if 90 == $formData.rotation}selected="selected"{/if}>90</option>
					<option value='180' {if 180 == $formData.rotation}selected="selected"{/if}>180</option>
					<option value='270' {if 270 == $formData.rotation}selected="selected"{/if}>270</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxpicture.denyvoting'}:
			</td>
			<td>
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyVoting}"{if $element->denyVoting} checked="checked"{/if}/>
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxpicture.denycomments'}:
			</td>
			<td>
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.denyComments}"{if $element->denyComments} checked="checked"{/if}/>
			</td>
		</tr>
		<tr {if $formErrors.image} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.image'}:
			</td>
			<td>
				{if $element->originalName != ""}
					<img src='{$element->getImageUrl()}' />
					<br/>
					<a href="{$element->URL}id:{$element->id}/action:deleteFile/file:image/" >{translations name='label.deleteimage'}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.image}" />
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