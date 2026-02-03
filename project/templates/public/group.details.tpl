{capture assign="moduleTitle"}
    {if $element->title}
        {translations name='group.title'}: {$element->title}
    {/if}
{/capture}
{capture assign="moduleContent"}
    {include file=$theme->template("component.groupinfo.tpl")}
    <div class="group_editing_controls editing_controls">
        {if isset($currentElementPrivileges.publicReceive) && $currentElementPrivileges.publicReceive}
            <a class="button"
               href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='group.edit'}</a>
        {/if}
        {if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}
            <a class="button" href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='group.upload_prods'}</a>
        {/if}
        {if !empty($currentElementPrivileges.join)}
            <a class="button" href="{$element->URL}id:{$element->id}/action:showJoinForm/">{translations name='group.join'}</a>
        {/if}
        {if isset($currentElementPrivileges.convertToAuthor) && $currentElementPrivileges.convertToAuthor}
            <a class="button convert_button" href="{$element->URL}id:{$element->id}/action:convertToAuthor/">{translations name='group.converttoauthor'}</a>
        {/if}
        {if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
            <a class="button delete_button"
               href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='group.delete'}</a>
        {/if}
    </div>
    <script>
        window.elementsData = window.elementsData ? window.elementsData : { };
        window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
    </script>

    {if $subGroups=$element->getSubGroups()}
        <h2>{translations name='group.subgroups'}</h2>
        <ul>
            {foreach from=$subGroups item=subgroup}
                <li><a href="{$subgroup->getUrl()}">{$subgroup->title}</a></li>
            {/foreach}
        </ul>
    {/if}
    {include file=$theme->template('component.groupauthors.tpl')}
    {include file=$theme->template('component.mentions.tpl')}
    {foreach $element->getAliasElements() as $aliasElement}
        {include file=$theme->template('component.groupauthors.tpl') element=$aliasElement}
    {/foreach}

    {include file=$theme->template('group.producedProds.tpl')}
	{include file=$theme->template('publisher.zxProds.tpl')}
	{include file=$theme->template('group.publishedReleases.tpl')}

    <zx-comments-list element-id="{$element->id}"></zx-comments-list>
{/capture}

{assign moduleClass "group_details"}
{assign moduleAttributes ''}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}