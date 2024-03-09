<div class="zxitem_images">
    {foreach $filesList as $file}
        {if $file->isImage()}
            <div class="zxitem_image_block">
                {if $file->author}
                    <a href="{$url}" class="zxitem_image_link" target="_blank">
                        <img loading="lazy" class="zxitem_image" src='{$file->getImageUrl($preset)}' alt="{$file->title}"/>
                    </a>
                    <div>by {$file->author}</div>
                {else}
                    <a href="{$file->getScreenshotUrl()}" class="zxitem_image_link">
                        <img loading="lazy" class="zxitem_image galleryimageid_{$file->id}" src='{$file->getImageUrl($preset)}'
                             alt="{$file->title}"/>
                    </a>
                    <a href="{$file->getScreenshotUrl()}" class="zxitem_image_download" title="{$file->getFileName(true)}"></a>
                {/if}
            </div>
        {/if}
    {/foreach}
</div>