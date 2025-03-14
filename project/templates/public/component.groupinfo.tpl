<div class="group-details-top">
	<table class='group_details_info info_table'>
		{if $aliasElements = $element->getAliasElements()}
		<tr>
			<td class='info_table_label'>
				{translations name='group.aliases'}:
			</td>
			<td class='info_table_value'>
				{foreach $aliasElements as $aliasElement}
					<a href="{$aliasElement->getUrl()}">{$aliasElement->title}</a>{if !$aliasElement@last}, {/if}
				{/foreach}
			</td>
		</tr>
		{/if}
		{if $parentGroups = $element->parentGroups}
		<tr>
			<td class='info_table_label'>
				{translations name='group.parent_groups'}:
			</td>
			<td class='info_table_value'>
				{foreach $parentGroups as $parentGroup}
					<a href="{$parentGroup->getUrl()}">{$parentGroup->title}</a>{if !$parentGroup@last}, {/if}
				{/foreach}
			</td>
		</tr>
		{/if}
		{if $element->type}
			<tr>
				<td class='info_table_label'>
					{translations name='group.type'}:
				</td>
				<td class='info_table_value'>
					{translations name="group.type_{$element->type}"}
				</td>
			</tr>
		{/if}
		{if $element->abbreviation}
			<tr>
				<td class='info_table_label'>
					{translations name='group.abbreviation'}:
				</td>
				<td class='info_table_value'>
					{$element->abbreviation}
				</td>
			</tr>
		{/if}
		{if $element->slogan}
			<tr>
				<td class='info_table_label'>
					{translations name='group.slogan'}:
				</td>
				<td class='info_table_value'>
					{$element->slogan}
				</td>
			</tr>
		{/if}
		{if $element->startDate}
			<tr>
				<td class='info_table_label'>
					{translations name='group.startdate'}:
				</td>
				<td class='info_table_value'>
					{$element->startDate}
				</td>
			</tr>
		{/if}
		{if $element->endDate}
			<tr>
				<td class='info_table_label'>
					{translations name='group.enddate'}:
				</td>
				<td class='info_table_value'>
					{$element->endDate}
				</td>
			</tr>
		{/if}
		{if $country = $element->getCountryElement()}
			<tr>
				<td class='info_table_label'>
					{translations name='group.country'}:
				</td>
				<td class='info_table_value'>
					<a href="{$country->getUrl('group')}">{$country->title}</a>
				</td>
			</tr>
		{/if}
		{if $city = $element->getCityElement()}
			<tr>
				<td class='info_table_label'>
					{translations name='group.city'}:
				</td>
				<td class='info_table_value'>
					<a href="{$city->getUrl('group')}">{$city->title}</a>
				</td>
			</tr>
		{/if}
		{if $element->website}
			<tr>
				<td class='info_table_label'>
					{translations name='group.website'}:
				</td>
				<td class='info_table_value'>
					<a class='newwindow_link' href="{$element->website}">{$element->website}</a>
				</td>
			</tr>
		{/if}
		{include file=$theme->template('component.links.tpl')}
	</table>
	<div class="group_details_photo">
		{if $element->originalName != ""}
			<img loading="lazy" class="group_details_photo_image" src='{$controller->baseURL}image/type:groupImage/id:{$element->image}/filename:{$element->originalName}' alt="{$element->title}" />
		{else}
			<img loading="lazy" class="group_details_photo_image" src='{$theme->getImageUrl('group.svg')}' alt="" />
		{/if}
	</div>
</div>