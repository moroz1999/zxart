{capture assign="moduleTitle"}{if $element->title}{$element->title}{/if}{/capture}
{capture assign="moduleContent"}
    <div class="zxrelease_editing_controls editing_controls">
        {if !empty($currentElementPrivileges.showPublicForm) && !empty($currentElementPrivileges.publicReceive)}
            <a class="button"
               href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='zxrelease.edit'}</a>
        {/if}
        {if isset($currentElementPrivileges.clone) && $currentElementPrivileges.clone==1}
            <a class="button"
               href="{$element->URL}id:{$element->id}/action:clone/">{translations name='zxrelease.clone'}</a>
        {/if}
        {if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
            <a class="button delete_button"
               href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='zxrelease.delete'}</a>
        {/if}
    </div>
    <div class="zxrelease-layout">
        <div class="zxrelease-layout-left">
            <table class='zxrelease_details_info info_table'>
                <tr>
                    <td class='info_table_label'>
                        {translations name='zxrelease.title'}:
                    </td>
                    <td class='info_table_value'>
                        {$element->title}
                    </td>
                </tr>
                {if $prod = $element->getProd()}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.prod'}:
                        </td>
                        <td class='info_table_value'>
                            <a href="{$prod->getUrl()}">{$prod->getTitle()} ({$prod->getYear()})</a>
                        </td>
                    </tr>
                {/if}
                {if $authors=$element->getAuthorsInfo('release')}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.authors'}:
                        </td>
                        <td class='info_table_value'>
                            {foreach $authors as $info}
                                <a
                                href="{$info.authorElement->getUrl()}">{$info.authorElement->title}</a>{if $info.roles} ({foreach $info.roles as $role}{translations name="zxprod.role_$role"}{if !$role@last}, {/if}{/foreach}){/if}{if !$info@last}, {/if}
                            {/foreach}
                        </td>
                    </tr>
                {/if}
                {if $element->hardwareRequired}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxRelease.hardwareRequired'}:
                        </td>
                        <td class='info_table_value'>
                            {include file=$theme->template("component.hardware.tpl") element=$element}
                        </td>
                    </tr>
                {/if}
                {if $element->language}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.language'}:
                        </td>
                        <td class='info_table_value'>
                            {include file=$theme->template("component.languagelinks.tpl") element=$element}
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td class='info_table_label'>
                        {translations name='zxrelease.legalstatus'}:
                    </td>
                    <td class='info_table_value'>
                        {translations name="legalstatus.{$element->getLegalStatus()}"}
                    </td>
                </tr>
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
                {if $element->year != '0'}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.year'}:
                        </td>
                        <td class='info_table_value'>
                            <a href="{$element->getCatalogueUrl(['years' => $element->getYear()])}">{$element->getYear()}</a>
                        </td>
                    </tr>
                {/if}
                {if $element->releaseFormat}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.releaseFormat'}:
                        </td>
                        <td class='info_table_value'>
                            {$releaseFormatsProvider = $element->getService(ZxArt\Releases\Services\ReleaseFormatsProvider::class)}
                            {foreach from=$element->releaseFormat item=format name=rf}
                                {if not $smarty.foreach.rf.first}, {/if}
                                <a href="{$element->getCatalogueUrlByFiletype($format)}" class="zxrelease-format-link">
                                    {$releaseFormatsProvider->getFormatEmoji($format)} {translations name="zxRelease.filetype_{$format}"}
                                </a>
                            {/foreach}
                        </td>
                    </tr>
                {/if}
                {if $element->releaseType}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.releaseType'}:
                        </td>
                        <td class='info_table_value'>
                            {translations name="zxRelease.type_{$element->releaseType}"}
                        </td>
                    </tr>
                {/if}
                {if $element->version}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.version'}:
                        </td>
                        <td class='info_table_value'>
                            {$element->version}
                        </td>
                    </tr>
                {/if}
                {$prod = $element->getProd()}
                {if $element->isDownloadable()}
                    {if $element->fileName}
                        <tr>
                            <td class='info_table_label'>
                                {translations name='zxrelease.file'}:
                            </td>
                            <td class='info_table_value'>
                                <a rel="nofollow"
                                   href="{$element->getFileUrl()}"><img
                                            loading="lazy"
                                            src="{$theme->getImageUrl("disk.png")}"
                                            alt="{translations name='label.download'} {$element->getFileName('original', false)}"/> {$element->fileName|urldecode}
                                </a>
                            </td>
                        </tr>
                    {/if}
                {elseif $prod->externalLink}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxprod.externallink'}:
                        </td>
                        <td class='info_table_value'>
                            {if $prod->getLegalStatus() === 'insales'}
                                <a class="button release-sales-button" href="{$prod->externalLink}"
                                   target="_blank">{translations name='zxprod.purchase'}</a>
                            {else}
                                <a class="button" href="{$prod->externalLink}"
                                   target="_blank">{translations name='zxprod.open_externallink'}</a>
                            {/if}
                        </td>
                    </tr>
                {/if}

                {include file=$theme->template('component.links.tpl')}
                {if $element->isDownloadable()}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.downloads'}:
                        </td>
                        <td class='info_table_value'>
                            {$element->downloads}
                        </td>
                    </tr>
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.plays'}:
                        </td>
                        <td class='info_table_value'>
                            {$element->plays}
                        </td>
                    </tr>
                {/if}
                {assign var="userElement" value=$element->getUserElement()}
                {if $userElement}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.addedby'}:
                        </td>
                        <td class='info_table_value'>
                            {$userElement->userName}, {$element->dateCreated}
                        </td>
                    </tr>
                {else}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxprod.added'}:
                        </td>
                        <td class='info_table_value'>
                            {$element->dateCreated}
                        </td>
                    </tr>
                {/if}
                {if $element->isDownloadable() && $element->isPlayable()}
                    <tr>
                        <td class='info_table_label'>
                            {translations name='zxrelease.play'}:
                        </td>
                        <td class='info_table_value'>
                            {include file=$theme->template('component.play-button.tpl') element=$element}
                        </td>
                    </tr>
                {/if}
            </table>

            {include $theme->template("component.emulator.tpl")}
            {if $pictures=$element->getPictures()}
                {include file=$theme->template('component.pictureslist.tpl') pictures=$pictures}
            {/if}

            <div class="gallery_static galleryid_{$element->id}">
                {if $filesList = $element->getFilesList('screenshotsSelector')}
                    <div class="zxrelease_editing_controls editing_controls">
                        {if isset($currentElementPrivileges.moveScreenshots) && $currentElementPrivileges.moveScreenshots}
                            <a class="button"
                               href="{$element->URL}id:{$element->id}/action:moveScreenshots/">{translations name='zxrelease.move_screenshots'}</a>
                        {/if}
                    </div>
                    {include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodImage' displayTitle=false linkType='screenshotsSelector'}
                {/if}

                {if $filesList = $element->getFilesList('inlayFilesSelector')}
                    <h3>{translations name='zxrelease.inlays'}</h3>
                    {include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodImage' linkType='inlayFilesSelector'}
                {/if}

                {if $filesList = $element->getFilesList('adFilesSelector')}
                    <h3>{translations name='zxrelease.ads'}</h3>
                    {include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodImage' linkType='adFilesSelector'}
                {/if}
            </div>
            <div class="zxrelease_description">
                {$element->description}
            </div>
        </div>
        {if $prod = $element->getProd()}
            <div class="zxrelease-layout-right">
                {if $filesList = $prod->getFilesList('connectedFile')}
                    {include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodImage' displayTitle=false linkType='connectedFile'}
                {/if}
            </div>
        {/if}
    </div>
    {if $filesList = $element->getFilesList('infoFilesSelector')}
        <h3>{translations name='zxrelease.instructions'}</h3>
        {include file=$theme->template('zxItem.files.tpl') filesList = $filesList newWindow=true}
    {/if}


    {if $element->compilations}
        <script>
            window.elementsData = window.elementsData ? window.elementsData : {};
            window.elementsData[{$element->id}] = {$element->getCompilationJsonData()};
        </script>
    {/if}
    {if $element->compilations}
        <h2>{translations name='zxprod.compilations'}</h2>
        <zx-prods-list element-id="{$element->id}" property="compilations"></zx-prods-list>
    {/if}

    {include file=$theme->template('component.comments.tpl')}
    {if $element->denyComments}<p>{translations name="zxitem.commentsdenied"}</p>{/if}

    {*{include file=$theme->template('component.voteslist.tpl')}*}
    {*{if $element->denyVoting}<p>{translations name="zxitem.votingdenied"}</p>{/if}*}

    {if $element->getTunes()}
        <h2>{translations name="zxrelease.music"}</h2>
        <div class="game_tunes">
            {include file=$theme->template("component.musictable.tpl") musicList=$element->getTunes() element=$element}
        </div>
    {/if}

    {if $element->parsed && $element->isDownloadable()}
        {if $structure = $element->getReleaseStructure()}
            <h2>{translations name="zxrelease.structure"}</h2>
            <table class="table_component zxrelease_filestructure_table">
                {include $theme->template('zxRelease.structure.tpl') structure=$structure level=0}
            </table>
        {/if}
    {/if}
    <script>
        /*<![CDATA[*/
        window.galleriesInfo = window.galleriesInfo || {ldelim}{rdelim};
        window.galleriesInfo['{$element->id}'] = {$element->getGalleryJsonInfo(['descriptionType'=>'hidden'], 'prodImage')};
        /*]]>*/
    </script>
    <script>
        /*<![CDATA[*/
        window.releasesInfo = window.releasesInfo ?? {ldelim}{rdelim};
        window.releasesInfo['{$element->id}'] = {$element->getJsonInfo('details')};
        /*]]>*/
    </script>
{/capture}

{assign moduleClass "zxrelease_details"}
{assign moduleAttributes "data-id={$element->id}"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}