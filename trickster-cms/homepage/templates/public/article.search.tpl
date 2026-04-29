{assign moduleTitle $element->searchTitle}
{capture assign="moduleSideContent"}{/capture}
{capture assign="moduleContent"}
	<div class='search_result_content'>
		{$resultElement->searchContent}
	</div>
{/capture}

{capture assign="moduleControls"}
	<a class="button" href='{$resultElement->URL}'>
		<span class='button_text'>
			{translations name='search.readmore'}
		</span>
	</a>
{/capture}

{assign moduleClass ""}
{assign moduleTitleClass "search_result_content_title"}
{assign moduleAttributes ""}
{assign moduleSideContentClass ""}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}