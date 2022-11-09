<div class="zxitem_files">
    {foreach $filesList as $file}
        <div class="zxitem_file_block">
            {if !empty($url)}
                <a href="{$url}" {if !empty($newWindow)}target="_blank" {/if}>{$file->getFileName(true)}</a>
            {else}
                <a href="{$file->getDownloadUrl('view', 'release')}" {if !empty($newWindow)}target="_blank" {/if}>{$file->getFileName(false)}</a>
            {/if}

            {if $file->author} by {$file->author}{/if}
        </div>
    {/foreach}
</div>