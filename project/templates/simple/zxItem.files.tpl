<div class="zxitem_files">
	{foreach $filesList as $file}
		<a href="{$file->getScreenshotUrl()}" class="zxitem_file_block">
			{$file->getFileName(false)}
		</a>
	{/foreach}
</div>