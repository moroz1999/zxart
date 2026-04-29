{assign moduleTitle $element->title}
{capture assign="moduleContent"}
<a class='gallery_short_link' href="{$element->URL}">
	{if $element->originalName != ""}
		{include file=$theme->template('component.elementimage.tpl') type='galleryShortImage' class='gallery_short_image' lazy=true}
	{/if}
</a>
{/capture}

{assign moduleClass 'gallery_short_block'}
{assign moduleTitleClass 'gallery_short_title'}
{include file=$theme->template('component.subcontentmodule_square.tpl')}