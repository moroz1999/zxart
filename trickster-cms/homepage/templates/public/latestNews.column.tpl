{assign var='formNames' value=$element->getFormNames()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='newsList' value=$element->getNewsList()}

{if $newsList}
	{capture assign="moduleTitle"}{$element->title}{/capture}	
	{capture assign="moduleContent"}		
		{foreach $newsList as $news}
			{include file=$theme->template($news->getTemplate($element->getCurrentLayout('column'))) element=$news}
		{/foreach}
	{/capture}

	{assign moduleClass "latestnews_column_block"}
	{assign moduleContentClass "latestnews_column_content_block"}
	{assign moduleTitleClass "latestnews_column_title"}

	{include file=$theme->template("component.columnmodule.tpl")}
{/if}