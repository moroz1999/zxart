{assign var='newsList' value=$element->getNewsList(true)}
{if $newsList}
	<div class="latestnews latestnews_footer latestnews_{$element->newsViewType}">
		<div class="latestnews_title">
			{$element->title}
		</div>
		{stripdomspaces}
			<div class="latestnews_news news_list">
				{foreach $newsList as $news}
					{include file=$theme->template($news->getTemplate($element->getCurrentLayout())) element=$news}
				{/foreach}
			</div>
		{/stripdomspaces}
	</div>
{/if}