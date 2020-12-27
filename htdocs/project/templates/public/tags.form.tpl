{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<h2>{translations name='label.edittags'}</h2>
<form action="{$element->URL}" method="post" class="picturetags_form" enctype="multipart/form-data">
	<div class="tag_suggestions">
		{foreach $element->getSuggestedTags() as $tag}
			<span class="button tag_suggestion_button">+ <span class="tag_suggestion_text">{$tag->title}</span>{if $tag->description} <span>({$tag->description})</span></span>{/if}</span>
		{/foreach}
	</div>
	<table class='form_table'>
		<tr class='{if $formErrors.tagsText} form_error{/if}'>
			<td class='form_label'>
				{translations name='field.tags'}:
			</td>
			<td class='form_field'>
				<input autocomplete="off" type="text" class="input_component picturetags_input" value="{$element->generateTagsText()}" name="{$formNames.tagsText}" />
			</td>
			<td>
				<button class="button form_button picturetags_submit">{translations name='button.send'}</button>
			</td>
		</tr>
	</table>
	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="submitTags" name="action" />
</form>