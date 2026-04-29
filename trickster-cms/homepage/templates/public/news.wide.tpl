{assign moduleTitle $element->title}
{capture assign="moduleSideContent"}{if $element->originalName}{include file=$theme->template('component.elementimage.tpl') type='newsShortImage' class='news_wide_image' lazy=true}{/if}{/capture}
{capture assign="moduleContent"}
	<div class='news_wide_introduction html_content'>
		{$element->introduction}
	</div>
	{if $element->date}
		<div class="news_wide_date">
			{$element->date}
		</div>
	{/if}
{/capture}
{if $element->content}
	{capture assign="moduleControls"}
		<a href="{$element->URL}" class='news_wide_readmore button'>
			<span class='button_text'>{translations name='news.news_readmore'}</span>
		</a>
	{/capture}
{/if}
{assign moduleClass "news_wide"}
{assign moduleTitleClass ''}
{assign moduleContentClass 'news_wide_content'}
{assign moduleAttributes ''}
{assign moduleSideContentClass "news_wide_image_block"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}