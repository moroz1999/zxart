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


            <div class="pressarticle-external-link">
                <a href="{$element->externalLink}"
                   target="_blank"
                >{translations name='pressarticle.source'}</a>
            </div>
            <div class="pressarticle-mentions-container">
                {if $element->authors}
                    <div class="pressarticle-mentions">
                        <h3>{translations name='pressarticle.authors'}</h3>
                        <div class="pressarticle-mentions-list{if count($element->authors) > 10} pressarticle-mentions-list-columns{/if}">
                        {foreach $element->getSorted($element->authors) as $author}
                            <div class="pressarticle-mentions-item">
                                <a href="{$author->getUrl()}">{$author->getSearchTitle()}</a>
                            </div>
                        {/foreach}
                        </div>
                    </div>
                {/if}
                {if $element->people}
                    <div class="pressarticle-mentions">
                        <h3>{translations name='pressarticle.people'}</h3>
                        <div class="pressarticle-mentions-list{if count($element->people) > 10} pressarticle-mentions-list-columns{/if}">
                            {foreach $element->getSorted($element->people) as $author}
                                <div class="pressarticle-mentions-item">
                                    <a href="{$author->getUrl()}">{$author->getSearchTitle()}</a>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
                {if $element->groups}
                    <div class="pressarticle-mentions">
                        <h3>{translations name='pressarticle.groups'}</h3>
                        <div class="pressarticle-mentions-list{if count($element->groups) > 10} pressarticle-mentions-list-columns{/if}">
                            {foreach $element->getSorted($element->groups) as $group}
                                <div class="pressarticle-mentions-item">
                                    <a href="{$group->getUrl()}">{$group->getSearchTitle()}</a>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
                {if $element->software}
                    <div class="pressarticle-mentions">
                        <h3>{translations name='pressarticle.software'}</h3>
                        <div class="pressarticle-mentions-list{if count($element->software) > 10} pressarticle-mentions-list-columns{/if}">
                            {foreach $element->getSorted($element->software) as $prod}
                                <div class="pressarticle-mentions-item">
                                    <a href="{$prod->getUrl()}">{$prod->getSearchTitle()}</a>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
                {if $element->pictures}
                    <div class="pressarticle-mentions">
                        <h3>{translations name='pressarticle.pictures'}</h3>
                        <div class="pressarticle-mentions-list{if count($element->pictures) > 10} pressarticle-mentions-list-columns{/if}">
                            {foreach $element->getSorted($element->pictures) as $picture}
                                <div class="pressarticle-mentions-item">
                                    <a href="{$picture->getUrl()}">{$picture->getSearchTitle()}</a>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
                {if $element->tunes}
                    <div class="pressarticle-mentions">
                        <h3>{translations name='pressarticle.tunes'}</h3>
                        <div class="pressarticle-mentions-list{if count($element->tunes) > 10} pressarticle-mentions-list-columns{/if}">
                            {foreach $element->getSorted($element->tunes) as $tune}
                                <div class="pressarticle-mentions-item">
                                    <a href="{$tune->getUrl()}">{$tune->getSearchTitle()}</a>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
                {if $element->parties}
                    <div class="pressarticle-mentions">
                        <h3>{translations name='pressarticle.parties'}</h3>
                        <div class="pressarticle-mentions-list{if count($element->parties) > 10} pressarticle-mentions-list-columns{/if}">
                            {foreach $element->getSorted($element->parties) as $party}
                                <div class="pressarticle-mentions-item">
                                    <a href="{$party->getUrl()}">{$party->getSearchTitle()}</a>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
            </div>

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