{capture assign="moduleContent"}
	<zx-search-results
		element-id="{$element->id}"
		base-url="{$element->URL}"
	></zx-search-results>
{/capture}

{assign moduleClass "search_results_block"}
{assign moduleContentClass "search_results_block"}
{include file=$theme->template("component.contentmodule.tpl")}
