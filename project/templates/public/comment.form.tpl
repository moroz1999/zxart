{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="comment_form" enctype="multipart/form-data">
	{if !$registeredOnly && $currentUser->userName === 'anonymous'}
		<div class='comment_form_author{if $formErrors.author} form_error{/if}'>
			<input class="input_component" name='{$formNames.author}' value="{$formData.author}" placeholder="{translations name='commentform.author'}">
		</div>
		<div class='comment_form_email{if $formErrors.email} form_error{/if}'>
			<input class="input_component" name='{$formNames.email}' value="{$formData.email}" type="email" placeholder="{translations name='commentform.email'}">
		</div>
	{/if}
	<div class='{if $formErrors.content} form_error{/if}'>
		<textarea class="textarea_component" name='{$formNames.content}' placeholder="{translations name='commentform.content'}">{$formData.content}</textarea>
	</div>
	<div class='form_controls'>
		<button class="form_button button">{translations name='comment.send'}</button>
	</div>
	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="publicReceive" name="action" />
</form>