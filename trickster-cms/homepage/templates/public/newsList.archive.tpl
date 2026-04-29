{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{include file=$theme->template('pager.tpl') pager=$element->getPager(true)}

	<div class="newslist_news">
		<table class="newslist_newstable table_component">
			{foreach $element->getArchiveNewsList() as $number=>$news}
				{include file=$theme->template("news.table.tpl") element=$news number=$number}
			{/foreach}
		</table>
	</div>

	{include file=$theme->template('pager.tpl') pager=$element->getPager(true)}
{/capture}

{assign moduleClass "newslist"}
{assign moduleTitleClass "newslist_title"}
{include file=$theme->template("component.contentmodule.tpl")}