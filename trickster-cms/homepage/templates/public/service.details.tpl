{capture assign="moduleContent"}

	{if $element->title}
		{capture assign="moduleTitle"}
			{$element->title}
		{/capture}
	{/if}

	{if $element->originalName != ""}
		{include file=$theme->template('component.elementimage.tpl') type='serviceDetailsImage' class='service_details_image'}
	{/if}
	<div class='service_details_content html_content'>
		{$element->content}
	</div>
	{if $contentList = $element->getContentList()}
		<div class="service_details_contentlist">
			{foreach $contentList as $contentItem}{include file=$theme->template($contentItem->getTemplate()) element=$contentItem}{/foreach}
		</div>
	{/if}
	{if $element->feedbackURL}
		<div class="service_details_controls">
			<a href="{$element->feedbackURL}" class="button service_details_askmore">
				<span class='button_text'>{translations name='service.details_askmore'}</span>
			</a>
		</div>
	{/if}
	{if $galleries = $element->getConnectedGalleries()}
		<h1>{translations name="service.galleries"}</h1>
		<div class="service_details_galleries">
			{foreach from=$galleries item=gallery}{include file=$theme->template($gallery->getTemplate($element->getGalleriesLayout())) element=$gallery}{/foreach}
		</div>
	{/if}
{/capture}

{assign moduleTitleClass "servicedetails_heading"}
{assign moduleClass "servicedetails_block"}
{assign moduleContentClass "service_details_block"}

{include file=$theme->template("component.contentmodule.tpl")}