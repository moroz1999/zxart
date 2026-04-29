{if $h1 = $element->getH1()}
	{capture assign="moduleTitle"}
		{$h1}
	{/capture}
{elseif $element->title}
	{capture assign="moduleTitle"}{$element->title}{/capture}
{/if}
{capture assign="moduleContent"}
	{if $element->originalName != ''}
		<div class="article_image_wrap">
			{include file=$theme->template('component.elementimage.tpl') type='articleDefaultImage' class='article_image' lazy=true}
		</div>
	{/if}
	{if !empty($element->content)}
		<div class="html_content">
			{$element->content}
		</div>
	{/if}
{/capture}
{assign moduleClass "article_block article_layout_{$element->getCurrentLayout()}"}
{assign moduleTitleClass "article_heading"}
{assign moduleContentClass "article_content"}
{include file=$theme->template("component.contentmodule.tpl")}