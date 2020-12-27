<div class="party_details_logo">
	<img class="party_details_logo_image" src='{$element->getImageUrl('partyDetails')}' alt="{$element->title}" />
</div>
<table class='party_details_info info_table'>
	<tr>
		<td class='info_table_label'>
			{translations name='field.title'}:
		</td>
		<td class='info_table_value'>
			{$element->title}
		</td>
	</tr>
	{if $element->abbreviation}
	<tr>
		<td class='info_table_label'>
			{translations name='field.abbreviation'}:
		</td>
		<td class='info_table_value'>
			{$element->abbreviation}
		</td>
	</tr>
	{/if}

	<tr>
		<td class='info_table_label'>
			{translations name='field.year'}:
		</td>
		<td class='info_table_value'>
			{$element->getYear()}
		</td>
	</tr>
	{if $country = $element->getCountryElement()}
		<tr>
			<td class='info_table_label'>
				{translations name='party.country'}:
			</td>
			<td class='info_table_value'>
				{if $country = $element->getCountryElement()}
					<a href="{$country->URL}">{$country->title}</a>
				{/if}
			</td>
		</tr>
	{/if}
	{if $city = $element->getCityElement()}
		<tr>
			<td class='info_table_label'>
				{translations name='field.city'}:
			</td>
			<td class='info_table_value'>
				{if $city = $element->getCityElement()}
					<a href="{$city->URL}">{$city->title}</a>
				{/if}
			</td>
		</tr>
	{/if}
</table>