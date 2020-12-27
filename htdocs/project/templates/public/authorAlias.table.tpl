<tr class="">
	<td class="author_table_number">
		{$number}
	</td>
	<td class="author_table_title">
		<a class='' href='{$element->getUrl()}'>{$element->title}</a>
	</td>
	{if $authorElement = $element->getAuthorElement()}
		<td class="author_table_groups">
			{foreach $element->getGroupsList() as $groupElement}<a href="{$groupElement->getUrl()}">{$groupElement->title}</a>{if !$groupElement@last}, {/if}{/foreach}
		</td>
		<td class="author_table_realname">
			<a href="{$authorElement->getUrl()}">{$authorElement->title}</a>
		</td>
		<td class="author_table_country">
			{if $country = $authorElement->getCountryElement()}
				<a href="{$country->URL}">{$country->title}</a>
			{/if}
		</td>
		<td class="author_table_city">
			{if $city = $authorElement->getCityElement()}
				<a href="{$city->URL}">{$city->title}</a>
			{/if}
		</td>
		<td class="author_table_musicrating">
			{if $authorElement->musicRating > 0}{$authorElement->musicRating}{/if}
		</td>
		<td class="author_table_graphicsrating">
			{if $authorElement->graphicsRating > 0}{$authorElement->graphicsRating}{/if}
		</td>
	{/if}
</tr>