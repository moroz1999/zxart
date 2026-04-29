{if $h1 = $element->getH1()}
	{capture assign="moduleTitle"}
		{$h1}
	{/capture}
{elseif $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{if $element->originalName}
		<div class="news_details_image_block">
			{include file=$theme->template('component.elementimage.tpl') type='newsDetailsImage' class='news_details_image'}
		</div>
	{/if}
	{if $element->date}
		<div class="news_details_date">
			{$element->date}
		</div>
	{/if}
	<div class='news_details_content html_content'>
		{if $element->content}
			{eval var=$element->content}
		{else}
			{$element->introduction}
		{/if}
	</div>
{/capture}

{assign moduleTitleClass "news_details_title"}
{assign moduleClass "news_details_block"}
{include file=$theme->template("component.contentmodule.tpl")}