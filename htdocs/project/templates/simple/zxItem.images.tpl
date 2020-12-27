{foreach $filesList as $file}
{if $file->isImage()}
	<img src='{$file->getImageUrl($preset)}' alt="{$file->title}" />
{/if}
{/foreach}