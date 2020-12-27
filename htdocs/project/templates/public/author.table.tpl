<tr class="">
	<td class="author_table_number">
		{$number}
	</td>
	<td class="author_table_title">
		<a class='' href='{$element->getUrl()}'>{$element->title}</a>
	</td>
	<td class="author_table_groups">
		{foreach $element->getGroupsList() as $groupElement}<a href="{$groupElement->getUrl()}">{$groupElement->title}</a>{if !$groupElement@last}, {/if}{/foreach}
	</td>
	<td class="author_table_realname">
		{$element->realName}
	</td>
	<td class="author_table_country">
		{if $country = $element->getCountryElement()}
			<a href="{$country->URL}">{$country->title}</a>
		{/if}
	</td>
	<td class="author_table_city">
		{if $city = $element->getCityElement()}
			<a href="{$city->URL}">{$city->title}</a>
		{/if}
	</td>
	<td class="author_table_musicrating">
		{if $element->musicRating > 0}{$element->musicRating}{/if}
	</td>
	<td class="author_table_graphicsrating">
		{if $element->graphicsRating > 0}{$element->graphicsRating}{/if}
	</td>
</tr>