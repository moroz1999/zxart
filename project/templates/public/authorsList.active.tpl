{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{assign 'activeAuthors' $element->getActiveAuthors()}
	{if $activeAuthors}
		<div class="authorslist_authors">
	    {foreach from=$activeAuthors item=author name=activeAuthors}
	        <a href="{$author->getUrl()}" class="active_author">{$author->title}</a>
	    {/foreach}
		</div>
	{/if}
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}