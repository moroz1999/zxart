{if $element->title}
	{capture assign="moduleTitle"}
		{translations name='author.title'}: {$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div class="author-right">
		{include file=$theme->template("component.authorimage.tpl")}
		{include file=$theme->template("component.authorbadges.tpl")}
	</div>
	{include file=$theme->template("component.authorinfo.tpl")}
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
			<a class="button" href="{$element->URL}type:authorAlias/action:showPublicForm/">{translations name='author.add_authoralias'}</a>
		{/if}
		{if isset($privileges.picturesUploadForm.batchUploadForm) && $privileges.picturesUploadForm.batchUploadForm == true}
			<a class="button" href="{$element->URL}type:picturesUploadForm/action:batchUploadForm/">{translations name='author.upload'}</a>
		{/if}
		{if isset($privileges.musicUploadForm.batchUploadForm) && $privileges.musicUploadForm.batchUploadForm == true}
			<a class="button" href="{$element->URL}type:musicUploadForm/action:batchUploadForm/">{translations name='author.upload_music'}</a>
		{/if}
		{if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}
			<a class="button" href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='author.upload_prods'}</a>
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
	<script>
		window.elementsData = window.elementsData ? window.elementsData : { };
		window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
	</script>
	{include file=$theme->template('author.articles.tpl')}
	{include file=$theme->template('component.mentions.tpl')}
	{include file=$theme->template('author.pictures.tpl')}
	{include file=$theme->template('author.music.tpl')}
	{include file=$theme->template('author.zxProds.tpl')}
	{include file=$theme->template('publisher.zxProds.tpl')}
	{include file=$theme->template('author.zxReleases.tpl')}

	<zx-comments-list element-id="{$element->id}"></zx-comments-list>

{/capture}

{assign moduleClass "author_details"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}