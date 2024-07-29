{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<script>
		window.elementsData = window.elementsData ? window.elementsData : { };
		window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
	</script>
	<app-zx-prods-list element-id="{$element->id}" property="prods"></app-zx-prods-list>
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}