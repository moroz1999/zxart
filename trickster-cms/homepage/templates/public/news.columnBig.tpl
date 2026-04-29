<div class="latest_news_column latest_news_column_big">
	<div class="latest_news_item_heading">{$element->title}</div>
	{if $element->originalName !=''}
		<div class="latest_news_big_image_block">
			{include file=$theme->template('component.elementimage.tpl') type='newsBigImage' class='latest_news_big_image' lazy=true}
		</div>
	{/if}
	{if $element->introduction !=''}
		<div class='latest_news_item_content'>
			{$element->introduction}
		</div>
	{/if}
	{if $element->content != ''}
		<div class="latest_news_item_controls">
			<a href="{$element->URL}" class='latest_news_item_readmore'>
				{translations name='news.news_short_readmore'}
			</a>
		</div>
	{/if}
</div>