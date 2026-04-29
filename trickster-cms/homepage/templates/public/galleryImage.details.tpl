{if !isset($moduleAttributes)}{assign 'moduleAttributes' ""}{/if}
{if !isset($thumbnailPreset)}{$thumbnailPreset="galleryThumbnailImage"}{/if}
{if !isset($captionLayout)}{$captionLayout=""}{/if}
{capture assign="moduleContent"}
	{if $element->originalName != ""}
		<a class="gallery_details_item_link" href="{$controller->baseURL}image/type:galleryFullImage/id:{$element->image}/filename:{$element->originalName}">
			{if $captionLayout == 'above'}
				<figcaption class="gallery_details_item_caption gallery_details_item_caption_{$captionLayout}">
					<span class="gallery_details_item_title">{$element->title}</span>
					<span class="gallery_details_item_description">{$element->description}</span>
				</figcaption>
			{/if}
			{include file=$theme->template('component.elementimage.tpl') type=$thumbnailPreset class='gallery_details_item_image' lazy=true title=$element->alt}
			{if $captionLayout == 'over'}
				{if $element->description || $element->title}
					<figcaption class="gallery_details_item_overlay gallery_details_item_caption gallery_details_item_caption_{$captionLayout}">
						{if $element->title}<span class="gallery_details_item_title">{$element->title}</span>{/if}
						{if $element->description}<span class="gallery_details_item_description">{$element->description}</span>{/if}
					</figcaption>
				{/if}
			{/if}
			{if $captionLayout == 'below'}
				<figcaption class="gallery_details_item_caption gallery_details_item_caption_{$captionLayout}">
					{if $element->title}<span class="gallery_details_item_title">{$element->title}</span>{/if}
					{if $element->description}<span class="gallery_details_item_description">{$element->description}</span>{/if}
				</figcaption>
			{/if}
		</a>
	{/if}
{/capture}
{assign "moduleClass" "gallery_details_item galleryimageid_"|cat:$element->id}
{assign moduleTitleClass ""}
{assign moduleTitle ""}
{assign moduleTag "figure"}
{$moduleAttributes=$moduleAttributes|cat:''}
{if !empty($columnWidth)}{$moduleAttributes=$moduleAttributes|cat:" style='width:{$columnWidth}%'"}{/if}
{include file=$theme->template("component.subcontentmodule_square.tpl")}