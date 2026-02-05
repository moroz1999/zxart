{capture assign="moduleContent"}
	<zx-comments-page title="{$element->title|escape:'html'}" url-base="{$element->URL}"></zx-comments-page>
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}
