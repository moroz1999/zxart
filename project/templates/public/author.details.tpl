<div class="author_editing_controls editing_controls">
	{if $element->getUserId() != $currentUser->id}
		{if isset($currentElementPrivileges.claim) && $currentElementPrivileges.claim == true}
			<a class="button" href="{$element->URL}id:{$element->id}/action:claim/">{translations name='author.claim'} "{$element->title}"</a>
		{/if}
	{/if}
	{if isset($currentElementPrivileges.publicReceive) && $currentElementPrivileges.publicReceive}
		<a class="button" href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='author.edit'}</a>
	{/if}
	{if isset($privileges.authorAlias.showPublicForm) && $privileges.authorAlias.showPublicForm == true}
		<a class="button button_primary" href="{$element->URL}type:authorAlias/action:showPublicForm/">{translations name='author.add_authoralias'}</a>
	{/if}
	{if isset($privileges.picturesUploadForm.batchUploadForm) && $privileges.picturesUploadForm.batchUploadForm == true}
		<a class="button button_primary" href="{$element->URL}type:picturesUploadForm/action:batchUploadForm/">{translations name='author.upload'}</a>
	{/if}
	{if isset($privileges.musicUploadForm.batchUploadForm) && $privileges.musicUploadForm.batchUploadForm == true}
		<a class="button button_primary" href="{$element->URL}type:musicUploadForm/action:batchUploadForm/">{translations name='author.upload_music'}</a>
	{/if}
	{if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}
		<a class="button button_primary" href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='author.upload_prods'}</a>
	{/if}
	{if !empty($currentElementPrivileges.join)}
		<a class="button" href="{$element->URL}id:{$element->id}/action:showJoinForm/">{translations name='author.join'}</a>
	{/if}
	{if isset($currentElementPrivileges.convertToGroup) && $currentElementPrivileges.convertToGroup}
		<a class="button convert_button" href="{$element->URL}id:{$element->id}/action:convertToGroup/">{translations name='author.converttogroup'}</a>
	{/if}
	{if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
		<a class="button delete_button" href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='author.delete'}</a>
	{/if}
</div>

<zx-author-details element-id="{$element->id}"></zx-author-details>
