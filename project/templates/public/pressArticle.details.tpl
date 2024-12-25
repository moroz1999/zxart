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
                {if isset($currentElementPrivileges.showAiForm) && $currentElementPrivileges.showAiForm==1}
                    <a class="button"
                       href="{$element->URL}id:{$element->id}/action:showAiForm/">{translations name='pressarticle.showAiForm'}</a>
                {/if}
                {if isset($currentElementPrivileges.delete) && $currentElementPrivileges.delete}
                    <a class="button delete_button"
                       href="{$element->URL}id:{$element->id}/action:delete/">{translations name='pressarticle.delete'}</a>
                {/if}
            </div>
            {if $element->getTagsList()}
                <div>
                    <div class='info_table_label'>
                        {translations name='zxprod.tags'}:
                    </div>
                    <div class='info_table_value'>
                        {foreach from=$element->getTagsList() item=tag name=tags}
                            <a href='{$tag->URL}'>{$tag->title}</a>{if !$smarty.foreach.tags.last}, {/if}
                        {/foreach}
                    </div>
                </div>
            {/if}


            <div class="">
                <a href="{$element->externalLink}"
                   target="_blank"
                >{translations name='pressarticle.source'}</a>
            </div>

            {if $element->authors}
                <h3>{translations name='pressarticle.authors'}</h3>
                {foreach $element->authors as $author}
                    <div>
                        <a href="{$author->getUrl()}">{$author->getTitle()}</a>
                    </div>
                {/foreach}
            {/if}

            {if $element->people}
                <h3>{translations name='pressarticle.people'}</h3>
                {foreach $element->people as $author}
                    <div>
                        <a href="{$author->getUrl()}">{$author->getTitle()}</a>
                    </div>
                {/foreach}
            {/if}

            {if $element->groups}
                <h3>{translations name='pressarticle.groups'}</h3>
                {foreach $element->groups as $group}
                    <div>
                        <a href="{$group->getUrl()}">{$group->getTitle()}</a>
                    </div>
                {/foreach}
            {/if}
            {if $element->software}
                <h3>{translations name='pressarticle.software'}</h3>
                {foreach $element->software as $prod}
                    <div>
                        <a href="{$prod->getUrl()}">{$prod->getTitle()}</a>
                    </div>
                {/foreach}
            {/if}
            {if $element->pictures}
                <h3>{translations name='pressarticle.pictures'}</h3>
                {foreach $element->pictures as $picture}
                    <div>
                        <a href="{$picture->getUrl()}">{$picture->getTitle()}</a>
                    </div>
                {/foreach}
            {/if}
            {if $element->tunes}
                <h3>{translations name='pressarticle.tunes'}</h3>
                {foreach $element->tunes as $tune}
                    <div>
                        <a href="{$tune->getUrl()}">{$tune->getTitle()}</a>
                    </div>
                {/foreach}
            {/if}
            {if $element->parties}
                <h3>{translations name='pressarticle.parties'}</h3>
                {foreach $element->parties as $party}
                    <div>
                        <a href="{$party->getUrl()}">{$party->getTitle()}</a>
                    </div>
                {/foreach}
            {/if}

            <pre class="pressarticle_content">{$element->getWrappedContent()}</pre>
            <h2>{translations name='pressarticle.morefromsame'}: <a href="{$parentElement->getUrl()}">{$parentElement->getTitle()}</a></h2>
            {include file=$theme->template('component.pressArticles.tpl') articles= $parentElement->articles pager=false}
        </div>
        <div class="pressarticle-layout-right">
            {if $filesList = $parentElement->getFilesList('connectedFile')}
                {include file=$theme->template('zxItem.images.tpl') filesList=$filesList preset='prodImage' displayTitle=false linkType='connectedFile'}
            {/if}
        </div>
    </div>
{/capture}

{assign moduleClass "pressarticle_details"}
{assign moduleAttributes ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}