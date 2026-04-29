{if !empty($element->newForm)}
	{assign var='element' value=$element->newForm}
{/if}

{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}

<div class="form_items">
	<div class="form_label">{translations name="gallery.select_images"}</div>
	<div class="form_field">
		<input class="fileinput_placeholder gallery_form_upload_input" type="file" name="{$formNames.image}" />
	</div>
	<div class="form_field"></div>
</div>
<div class="form_items">
	<div class="form_label">{translations name="gallery.select_images2"}</div>
	<div class="form_field">
		<div class="gallery_form_upload_droparea">{translations name="gallery.drag_images"}</div>
	</div>
	<div class="form_field">
		<input class="button gallery_form_upload_submit" type="submit" value='{translations name="button.upload"}' />
	</div>
</div>
<input class='gallery_form_upload_elementid_input' type="hidden" value="{$element->URL}" name="id" />
{if isset($linkType)}
	<input class='gallery_form_upload_linktype' type="hidden" value="{$linkType}" name="id" />
{/if}
<input class='gallery_form_upload_action_input' type="hidden" value="receive" name="action" />