{assign moduleTitle $element->title}
{if $element->originalName}
	{capture assign="moduleSideContent"}
		{include file=$theme->template('component.elementimage.tpl') type='newstinyImage' class='news_tiny_image' lazy=true}
	{/capture}
{/if}
{capture assign="moduleContent"}
		<div class='news_tiny_content html_content'>
			{$element->introduction}
		</div>
{/capture}
{if $element->content}
	{capture assign="moduleControls"}
		<a href="{$element->URL}" class='news_tiny_readmore button'>
			<span class='button_text'>{translations name='news.news_tiny_readmore'}</span>
		</a>
	{/capture}
{/if}

{assign moduleClass "news_tiny"}
{assign moduleTitleClass "news_tiny_title"}
{assign moduleSideContentClass "news_tiny_image_block"}
{include file=$theme->template("component.subcontentmodule_square.tpl")}