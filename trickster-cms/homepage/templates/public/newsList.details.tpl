{$layout = $element->layout|default:'short'}
{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}{stripdomspaces}
	{include file=$theme->template('pager.tpl') pager=$element->getPager()}
	<div class="newslist_news_grid news_list">
		{$template = $theme->template("news.{$layout}.tpl")}
		{foreach $element->getNewsList() as $news}
            {include file=$template element=$news}
		{/foreach}
	</div>
	{include file=$theme->template('pager.tpl') pager=$element->getPager()}

	{if $element->archiveEnabled}
		<div class="newslist_controls">
			<a class="newslist_archivebutton button" href="{$element->URL}id:{$element->id}/action:archive/">
				<span class="button_text">
					{translations name='news.archive'}
				</span>
			</a>
		</div>
	{/if}{/stripdomspaces}
{/capture}

{assign moduleClass "newslist newslist_layout_{$layout}"}
{assign moduleTitleClass "newslist_title"}
{include file=$theme->template("component.contentmodule.tpl")}