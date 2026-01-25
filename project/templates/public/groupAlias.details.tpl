{if $element->title}
	{capture assign="moduleTitle"}
		{translations name='groupalias.title'}: {$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{include file=$theme->template("groupAlias.info.tpl")}
	<div class="group_editing_controls editing_controls">
		{if isset($currentElementPrivileges.publicReceive) && $currentElementPrivileges.publicReceive}
			<a class="button" href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='groupalias.edit'}</a>
		{/if}
		{if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}
			<a class="button" href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='groupalias.upload_prods'}</a>
		{/if}
		{if !empty($currentElementPrivileges.join)}
			<a class="button" href="{$element->URL}id:{$element->id}/action:showJoinForm/">{translations name='groupalias.join'}</a>
		{/if}
		{if isset($currentElementPrivileges.convertToGroup) && $currentElementPrivileges.convertToGroup}
			<a class="button convert_button" href="{$element->URL}id:{$element->id}/action:convertToGroup/">{translations name='group.convertToGroup'}</a>
		{/if}
		{if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
			<a class="button delete_button" href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='groupalias.delete'}</a>
		{/if}
	</div>
	<script>
		window.elementsData = window.elementsData ? window.elementsData : { };
		window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
	</script>
	{if $authors=$element->getAuthorsInfo('group')}
		{include file=$theme->template('component.groupauthors.tpl')}
	{/if}
	{include file=$theme->template('component.mentions.tpl')}
	{include file=$theme->template('group.zxProds.groups.tpl')}
	{include file=$theme->template('publisher.zxProds.tpl')}
	{include file=$theme->template('group.publishedReleases.tpl')}

	<app-comments-list element-id="{$element->id}"></app-comments-list>
{/capture}

{assign moduleClass "group_details"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}