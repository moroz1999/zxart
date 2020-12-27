<tr class="">
	<td>
		{$number}
	</td>
	<td>
		<a class='' href='{$element->getUrl()}'>{$element->title}</a>
	</td>
	<td></td>
	<td>
		{if $country = $element->getCountryElement()}
			<a href="{$country->URL}">{$country->title}</a>
		{/if}
	</td>
	<td>
		{if $city = $element->getCityElement()}
			<a href="{$city->URL}">{$city->title}</a>
		{/if}
	</td>
</tr>