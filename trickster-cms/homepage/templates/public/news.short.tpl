{assign moduleTitle $element->title}
{if $element->originalName}
	{capture assign="moduleSideContent"}
		{include file=$theme->template('component.elementimage.tpl') type='newsShortImage' class='news_short_image' lazy=true}
	{/capture}
{/if}
{capture assign="moduleContent"}
		<div class='news_short_content html_content'>
			{$element->introduction}
		</div>
{/capture}
{if $element->content}
	{capture assign="moduleControls"}
		<a href="{$element->URL}" class='news_short_readmore button'>
			<span class='button_text'>{translations name='news.news_short_readmore'}</span>
		</a>
	{/capture}
{/if}

{assign moduleClass "news_short"}
{assign moduleTitleClass "news_short_title"}
{assign moduleSideContentClass "news_short_image_block"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}