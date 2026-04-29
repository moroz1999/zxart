{if $element->images}
	<div class="header_gallery_container">
		<div class="header_gallery galleryid_{$element->id} gallery_slide">
			<script>
				window.galleriesInfo = window.galleriesInfo || {ldelim}{rdelim};
				window.galleriesInfo['{$element->id}'] = {$element->getGalleryJsonInfo([
				'imagesButtonsEnabled'=>true,
				'descriptionType'=>'static',
				'changeDelay'=>4000,
				'thumbnailsSelectorEnabled'=> false,
				'galleryResizeType'=>'auto',
				'imageResizeType'=>'fill',
				'fullScreenGalleryEnabled'=>false
				], 'headerGallery', 'desktop')};
				</script>
			{* You can use this for ordering or wrapping gallery components
			<div class="gallery_structure">
				<div class="gallery_images_container"></div>
				<div class="gallery_buttons"></div>
				<div class="gallery_buttons_prevnext"></div>
				<div class="gallery_description_container"></div>
			</div>*}
		</div>
	</div>
{/if}