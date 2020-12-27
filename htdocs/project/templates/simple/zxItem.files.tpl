<div class="zxitem_files">
	{foreach $filesList as $file}
		<a href="{$file->getDownloadUrl('view', 'release')}" class="zxitem_file_block">
			{$file->getFileName(true)}
		</a>
	{/foreach}
</div>