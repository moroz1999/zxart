{if empty($linkType)}{$linkType = null}{/if}
<div class="zxitem_images">
    {foreach $filesList as $index => $file}
        {if $file->isImage()}
            <div class="zxitem_image_block">
                {if $file->author}
                    <a href="{$url}" class="zxitem_image_link" target="_blank">
                        <img loading="lazy" class="zxitem_image" src='{$file->getImageUrl($preset)}'
                             alt="{$file->title}"/>
                    </a>
                    <div>by {$file->author}</div>
                {else}
                    <a href="{$file->getScreenshotUrl()}" class="zxitem_image_link">
                        <img loading="lazy" class="zxitem_image galleryimageid_{$file->id}"
                             src='{$file->getImageUrl($preset)}'
                             alt="{$file->title}"/>
                    </a>
                    <div class="zxitem_image_controls">
                        {if !empty($currentElementPrivileges.showPublicForm) && $linkType !== null}
                            {if $index > 0}
                                <a href="{$element->getUrl('moveScreenshot')}fileId:{$file->getId()}/linkType:{$linkType}/direction:left"
                                   class="zxitem_image_button zxitem_image_left" title="{$file->getFileName(true)}"></a>
                            {/if}
                            {if $index < $filesList|@count - 1}
                                <a href="{$element->getUrl('moveScreenshot')}fileId:{$file->getId()}/linkType:{$linkType}/direction:right"
                                   class="zxitem_image_button zxitem_image_right"
                                   title="{$file->getFileName(true)}"></a>
                            {/if}
                        {/if}
                        <a href="{$file->getScreenshotUrl()}" class="zxitem_image_button zxitem_image_download"
                           title="{$file->getFileName(true)}"></a>
                    </div>
                {/if}
            </div>
        {/if}
    {/foreach}
</div>