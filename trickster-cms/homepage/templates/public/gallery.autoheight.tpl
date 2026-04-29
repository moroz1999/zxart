{if $h1 = $element->getH1()}
	{capture assign="moduleTitle"}
		{$h1}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{if $element->content!=''}
	<div class='gallery_details_content html_content'>
		{$element->content}
	</div>
	{/if}

	{if $element->images}
	{stripdomspaces}
			<div class="gallery_details_images gallery_static galleryid_{$element->id}">
				{$imagesInRow=0}
				{foreach $element->images as $image}
					{if $imagesInRow == 0}<div class="blocks_autoheight_component">{/if}
					{include file=$theme->template($image->getTemplate()) element=$image captionLayout=$element->captionLayout thumbnailPreset="galleryThumbnailUnevenImage"}
					{$imagesInRow = $imagesInRow + 1}
					{if $imagesInRow == $element->getColumns() || $image@last}</div>{$imagesInRow=0}{/if}
				{/foreach}
			</div>
	{/stripdomspaces}
	{/if}

	<script>

		window.galleriesInfo = window.galleriesInfo || {ldelim}{rdelim};
		window.galleriesInfo['{$element->id}'] = {$element->getGalleryJsonInfo([], 'gallery')};

	</script>

	{if $element->serviceElement}
	<div class="gallery_details_controls">
		<a href="{$element->serviceElement->URL}" class="button gallery_details_service">
			<span class='button_text'>{$element->serviceElement->title}</span>
		</a>
	</div>
	{/if}
{/capture}

{assign moduleClass "gallery_details gallery_autoheight"}
{assign moduleTitleClass "gallery_details_heading"}

{include file=$theme->template("component.contentmodule.tpl")}