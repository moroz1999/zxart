{assign moduleTitle $element->title}
{if $element->originalName}
	{capture assign="moduleSideContent"}
        {if $element->originalName}
            {include file=$theme->template('component.elementimage.tpl') type='subArticleShortImage' class='subarticle_simple_image' lazy=false}
        {/if}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div class='subarticle_simple_content html_content'>
		{$element->content}
	</div>
{/capture}
{assign moduleClass "subarticle_simple"}
{assign moduleTitleClass "subarticle_simple_title"}
{assign moduleSideContentClass "subarticle_simple_image_block"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}