<div class='parties_list_block'>
	<table class='parties_list_table table_component'>
		<thead>
			<tr>
				<th>
					{translations name='label.table_party'}
				</th>
				<th>
					{translations name='label.table_country'}
				</th>
				<th>
					{translations name='label.table_city'}
				</th>
				<th>
					{translations name='label.table_year'}
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$partiesList item=party name=partiesList}
				<tr class="">
					<td>
						<a class='' href='{$party->URL}'>{$party->title}</a>
					</td>
					<td>
						{if $country = $party->getCountryElement()}
							<a href="{$country->URL}">{$country->title}</a>
						{/if}
					</td>
					<td>
						{if $city = $party->getCityElement()}
							<a href="{$city->URL}">{$city->title}</a>
						{/if}
					</td>
					<td>
						{$party->getYear()}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>