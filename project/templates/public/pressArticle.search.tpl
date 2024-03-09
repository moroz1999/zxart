{assign moduleTitle $element->getSearchTitle()}
{capture assign="moduleSideContent"}{/capture}
{capture assign="moduleContent"}
	<pre class='search_result_content'>
		{$resultElement->getSearchContent()}
	</pre>
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