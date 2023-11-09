{$parentElement=$element->getParent()}
{capture assign="moduleTitle"}{$element->getH1()}{/capture}
{capture assign="moduleContent"}
    <div class="pressarticle-layout">
        <div class="pressarticle-layout-left">

            <div class="pressarticle_editing_controls editing_controls">
                {if isset($currentElementPrivileges.showPublicForm) && $currentElementPrivileges.showPublicForm==1}
                    <a class="button"
                       href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='pressarticle.edit'}</a>
                {/if}
                {if isset($currentElementPrivileges.delete) && $currentElementPrivileges.delete}
                    <a class="button delete_button"
                       href="{$element->URL}id:{$element->id}/action:delete/">{translations name='pressarticle.delete'}</a>
                {/if}
            </div>
            <div class="">
                <a href="{$element->externalLink}"
                   target="_blank"
                >{translations name='pressarticle.source'}</a>
            </div>
            <pre>{$element->content}</pre>
            <h2>{translations name='pressarticle.morefromsame'}: <a href="{$parentElement->getUrl()}">{$parentElement->getTitle()}</a></h2>
            {include file=$theme->template('component.pressArticles.tpl') articles= $parentElement->articles pager=false}
        </div>
        <div class="pressarticle-layout-right">
            {if $filesList = $parentElement->getFilesList('connectedFile')}
                {include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodImage' displayTitle=false}
            {/if}
        </div>
    </div>
{/capture}

{assign moduleClass "pressarticle_details"}
{assign moduleAttributes ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}