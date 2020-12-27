<div class="zxitem_images">
	{foreach $filesList as $file}
		{if $file->isImage()}
			<div class="zxitem_image_block">
				<a href="{$file->getDownloadUrl('view', 'release')}" class="zxitem_image_link">
					<img class="zxitem_image galleryimageid_{$file->id}" src='{$file->getImageUrl($preset)}' alt="{$file->title}" />
				</a>
				{if !empty($displayTitle)}
					<a href="{$file->getDownloadUrl('view', 'release')}" class="zxitem_image_title">
						{$file->getFileName(true)}
					</a>
				{/if}
			</div>
		{/if}
	{/foreach}
</div>