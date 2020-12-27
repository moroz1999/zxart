{$moduleTitle = $element->getH1()}
{capture assign="moduleContent"}
    {if !empty($element->youtubeId)}
        <div class="zxprod_details_video">
            <div class="zxprod_details_video_inner">
                <iframe class="zxprod_details_video_iframe" width="320" height="240"
                        src="https://www.youtube.com/embed/{$element->youtubeId}" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
        </div>
    {/if}
    <div class="zxprod_editing_controls editing_controls">
        {if isset($currentElementPrivileges.showPublicForm) && $currentElementPrivileges.showPublicForm==1}
            <a class="button"
               href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='zxprod.edit'}</a>
        {/if}
        {if !empty($currentElementPrivileges.join)}
            <a class="button"
               href="{$element->URL}id:{$element->id}/action:showJoinForm/">{translations name='zxprod.join'}</a>
        {/if}
        {if !empty($currentElementPrivileges.split)}
            <a class="button"
               href="{$element->URL}id:{$element->id}/action:showSplitForm/">{translations name='zxprod.split'}</a>
        {/if}
        {if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
            <a class="button delete_button"
               href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='zxprod.delete'}</a>
        {/if}
    </div>
    <table class='zxprod_details_info info_table'>
        <tr>
            <td class='info_table_label'>
                {translations name='zxprod.title'}:
            </td>
            <td class='info_table_value'>
                {$element->title}
            </td>
        </tr>
        {if $categories = $element->getConnectedCategories()}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.categories'}:
                </td>
                <td class='info_table_value'>
                    {foreach $categories as $categoryElement}
                        <a
                        href="{$categoryElement->URL}">{$categoryElement->title}</a>{if !$categoryElement@last}, {/if}
                    {/foreach}
                </td>
            </tr>
        {/if}
        {if $element->externalLink}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.externallink'}:
                </td>
                <td class='info_table_value'>
                    <a class="button" href="{$element->externalLink}"
                       target="_blank">{translations name='zxprod.open_externallink'}</a>
                </td>
            </tr>
        {/if}
        {if $element->language}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.language'}:
                </td>
                <td class='info_table_value'>
                    {$element->getSupportedLanguageString()}
                </td>
            </tr>
        {/if}
        <tr>
            <td class='info_table_label'>
                {translations name='zxProd.legalstatus'}:
            </td>
            <td class='info_table_value'>
                {translations name="legalstatus.{$element->getLegalStatus()}"}
            </td>
        </tr>
        {if $element->groups}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.groups'}:
                </td>
                <td class='info_table_value'>
                    {foreach $element->groups as $group}
                        <a href="{$group->getUrl()}">{$group->title}</a>{if !$group@last}, {/if}
                    {/foreach}
                </td>
            </tr>
        {/if}
        {if $element->publishers}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.publishers'}:
                </td>
                <td class='info_table_value'>
                    {foreach $element->publishers as $publisher}
                        <a href="{$publisher->getUrl()}">{$publisher->title}</a>{if !$publisher@last}, {/if}
                    {/foreach}
                </td>
            </tr>
        {/if}
        {if $authors=$element->getAuthorsInfo('prod')}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.authors'}:
                </td>
                <td class='info_table_value'>
                    {foreach $authors as $info}
                        <a
                        href="{$info.authorElement->getUrl()}">{$info.authorElement->title}</a>{if $info.roles} ({foreach $info.roles as $role}{translations name="zxprod.role_$role"}{if !$role@last}, {/if}{/foreach}){/if}{if !$info@last}, {/if}
                    {/foreach}
                </td>
            </tr>
        {/if}
        {if $element->getPartyElement()}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.party'}:
                </td>
                <td class='info_table_value'>
                    {assign 'compoTitle' "compo_"|cat:$element->compo}
                    <a href='{$element->getPartyElement()->URL}'>{$element->getPartyElement()->title}</a>
                    {if !empty($element->compo)}({if !empty($element->partyplace)}{$element->partyplace}, {/if}{translations name="party.$compoTitle"}){/if}
                </td>
            </tr>
        {/if}
        {if $element->year != '0'}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.year'}:
                </td>
                <td class='info_table_value'>
                    <a
                            href="{$picturesDetailedSearchElement->URL}startYear:{$element->year}/endYear:{$element->year}/">{$element->year}</a>
                </td>
            </tr>
        {/if}
        {if $element->getTagsList()}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.tags'}:
                </td>
                <td class='info_table_value'>
                    {foreach from=$element->getTagsList() item=tag name=tags}
                        <a href='{$tag->URL}'>{$tag->title}</a>{if !$smarty.foreach.tags.last}, {/if}
                    {/foreach}
                </td>
            </tr>
        {/if}
        {include file=$theme->template('component.links.tpl')}
        <tr>
            <td class='info_table_label'>
                {translations name='zxprod.votes'}:
            </td>
            <td class='info_table_value'>
                {include file=$theme->template("component.votecontrols.tpl") element=$element}
                {include file=$theme->template("component.playlist.tpl") element=$element}
                {if !$element->isVotingDenied() && $element->getVotePercent()}
                    <div>{$element->votes}</div>
                {/if}
            </td>
        </tr>
        {assign var="userElement" value=$element->getUser()}
        {if $userElement}
            <tr>
                <td class='info_table_label'>
                    {translations name='zxprod.addedby'}:
                </td>
                <td class='info_table_value'>
                    {$userElement->userName}, {$element->dateCreated}
                </td>
            </tr>
        {/if}
    </table>
    <div class="zxprod_releases_controls editing_controls">
        {if isset($privileges.zxRelease.publicAdd) && $privileges.zxRelease.publicAdd == true}
            <a class="button"
               href="{$element->URL}type:zxRelease/action:showPublicForm/">{translations name='zxprod.addrelease'}</a>
        {/if}
    </div>
    {include $theme->template("component.emulator.tpl")}
    <div class="gallery_static galleryid_{$element->id}">
        {if $releasesList=$element->getReleasesList()}
            <div class="zxprod_details_releases">
                {include file=$theme->template('component.releasestable.tpl') releasesList=$releasesList pager=false}
            </div>
        {/if}
        {if $filesList = $element->getFilesList('connectedFile')}
            {include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodImage' displayTitle=false}
        {/if}
        {if $filesList = $element->getFilesList('mapFilesSelector')}
            <h3>{translations name='zxprod.maps'}</h3>
            {include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodMapImage' displayTitle=true}
        {/if}
    </div>
    <script>
        /*<![CDATA[*/
        window.galleriesInfo = window.galleriesInfo || {ldelim}{rdelim};
        window.galleriesInfo['{$element->id}'] = {$element->getGalleryJsonInfo(['descriptionType'=>'hidden', 'imageResizeLogics'=>'contain'], 'prodImage')};
        /*]]>*/
    </script>
    {if $element->description}
        <div class="zxprod_details_description">
            {$element->description}
        </div>
    {/if}

    {if $element->compilationProds}
        <h3>{translations name='zxprod.compilationProds'}</h3>
        <div class="zxprods_list">
            {foreach $element->compilationProds as $prod}
                {include file=$theme->template('zxProd.short.tpl') element=$prod}
            {/foreach}
        </div>
    {/if}


    {include file=$theme->template('component.comments.tpl')}
    {if $element->denyComments}<p>{translations name="zxitem.commentsdenied"}</p>{/if}

    {include file=$theme->template('component.voteslist.tpl')}
    {if $element->denyVoting}<p>{translations name="zxitem.votingdenied"}</p>{/if}

    {include file=$theme->template('component.pictureslist.tpl') pictures=$element->getPictures() class="game_graphics"}

    {if $element->getTunes()}
        <div class="game_tunes">
            {include file=$theme->template("component.musictable.tpl") musicList=$element->getTunes() element=$element}
        </div>
    {/if}
    <script>
        if (!window.prodsList) {
            window.prodsList = [];
        }
        window.prodsList.push({$element->getJsonInfo()});
    </script>
{/capture}

{assign moduleClass "zxprod_details gallery_pictures"}
{assign moduleAttributes "id='gallery_{$currentElement->id}'"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}