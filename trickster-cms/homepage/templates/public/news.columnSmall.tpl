<div class="latest_news_column latest_news_column_small">
	<div class="latest_news_item_heading">{$element->title}</div>

	{if $element->introduction || $element->originalName}

		{if $element->originalName !=''}
			<div class="latest_news_small_image_block">
				{include file=$theme->template('component.elementimage.tpl') type='newsSmallImage' class='latest_news_small_image' lazy=true}
			</div>
		{/if}
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