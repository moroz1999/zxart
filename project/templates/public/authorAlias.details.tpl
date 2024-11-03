{if $element->title}
	{capture assign="moduleTitle"}
		{translations name='authoralias.title'}: {$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
{if $authorElement=$element->getAuthorElement()}
	<div class="author-right">
		{include file=$theme->template("component.authorimage.tpl") element=$authorElement}
		{include file=$theme->template("component.authorbadges.tpl")}
	</div>
	{include file=$theme->template("authorAlias.info.tpl")}
{/if}

	<div class="author_editing_controls editing_controls">
		{if isset($currentElementPrivileges.publicReceive) && $currentElementPrivileges.publicReceive}
			<a class="button" href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='authoralias.edit'}</a>
		{/if}
		{if isset($privileges.picturesUploadForm.batchUploadForm) && $privileges.picturesUploadForm.batchUploadForm == true}
			<a class="button" href="{$element->URL}type:picturesUploadForm/action:batchUploadForm/">{translations name='author.upload'}</a>
		{/if}
		{if isset($privileges.musicUploadForm.batchUploadForm) && $privileges.musicUploadForm.batchUploadForm == true}
			<a class="button" href="{$element->URL}type:musicUploadForm/action:batchUploadForm/">{translations name='author.upload_music'}</a>
		{/if}
		{if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}
			<a class="button" href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='party.upload_prods'}</a>
		{/if}
		{if isset($currentElementPrivileges.convertToAuthor) && $currentElementPrivileges.convertToAuthor}
			<a class="button convert_button" href="{$element->URL}id:{$element->id}/action:convertToAuthor/">{translations name='authoralias.convertToAuthor'}</a>
		{/if}
		{if !empty($currentElementPrivileges.join)}
			<a class="button" href="{$element->URL}id:{$element->id}/action:showJoinForm/">{translations name='authoralias.join'}</a>
		{/if}
		{if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
			<a class="button delete_button" href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='authoralias.delete'}</a>
		{/if}
	</div>
	<script>
		window.elementsData = window.elementsData ? window.elementsData : { };
		window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
	</script>

	{include file=$theme->template('author.pictures.tpl')}
	{include file=$theme->template('author.music.tpl')}
	{include file=$theme->template('author.zxProds.tpl')}
	{include file=$theme->template('publisher.zxProds.tpl')}
	{include file=$theme->template('author.zxReleases.tpl')}
	{include file=$theme->template('author.articles.tpl')}

	{include $theme->template('component.comments.tpl')}
{/capture}

{assign moduleClass "author_details"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}