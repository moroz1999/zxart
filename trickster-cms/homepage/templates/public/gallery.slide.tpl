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
			<div class="gallery_details_images gallery_{$element->getSlideType()} galleryid_{$element->id}">
				{foreach from=$element->images item=image name=gallery}
					{include file=$theme->template($image->getTemplate()) element=$image captionLayout=$element->captionLayout columnWidth=$element->getColumnWidth()}
				{/foreach}
			</div>
	{/stripdomspaces}
	{/if}

	<script>

		window.galleriesInfo = window.galleriesInfo || {ldelim}{rdelim};
		window.galleriesInfo['{$element->id}'] = {$element->getGalleryJsonInfo([
			'galleryResizeType' => 'aspected',
			'galleryHeight' => 0.5625,
			'thumbnailsSelectorEnabled' => true
		], 'gallery', 'desktop')};

	</script>

	{if $element->serviceElement}
	<div class="gallery_details_controls">
		<a href="{$element->serviceElement->URL}" class="button gallery_details_service">
			<span class='button_text'>{$element->serviceElement->title}</span>
		</a>
	</div>
	{/if}
{/capture}

{assign moduleClass "gallery_details"}
{assign moduleTitleClass "gallery_details_heading"}

{include file=$theme->template("component.contentmodule.tpl")}