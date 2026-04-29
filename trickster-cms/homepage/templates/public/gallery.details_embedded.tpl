<h2>{$element->title}</h2>
{if count($element->images)}
	{stripdomspaces}
	<div class="gallery_details_images gallery_static galleryid_{$element->id}">
		{foreach from=$element->images item=image name=gallery}
			{include file=$theme->template($image->getTemplate()) element=$image captionLayout=$element->captionLayout columnWidth=$element->getColumnWidth()}
		{/foreach}
	</div>
	{/stripdomspaces}
{/if}

<script>

	window.galleriesInfo = window.galleriesInfo || {ldelim}{rdelim};
	window.galleriesInfo['{$element->id}'] = {$element->getGalleryJsonInfo()};

</script>

{if $element->serviceElement}
	<div class="gallery_details_controls">
		<a href="{$element->serviceElement->URL}" class="button gallery_details_service">
			<span class='button_text'>{$element->serviceElement->title}</span>
		</a>
	</div>
{/if}