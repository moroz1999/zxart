<div class="comment_controls">
	{assign "elementPrivileges" $element->getPrivileges()}
	{if (isset($elementPrivileges.publicForm) && $elementPrivileges.publicForm) && $element->isEditable()}
		<a class="comment_edit_button button" href="{$element->getUrl()}id:{$element->id}/action:publicForm/">{translations name='comment.edit'}</a>
	{/if}
	{if (isset($elementPrivileges.delete) && $elementPrivileges.delete) && $element->isEditable()}
		<form action="{$element->getUrl()}" method="post" class="comment_delete_form">
			<button class="comment_delete_button button delete_button" onclick="return confirm('{translations name='comment.confirm_delete'}')">{translations name='comment.delete'}</button>
			<input type="hidden" value="{$element->id}" name="id" />
			<input type="hidden" value="delete" name="action" />
		</form>
	{/if}
</div>