<table class='author_details_info info_table'>
	{if $groupsList = $element->getGroupsList()}
		<tr>
			<td class='info_table_label'>
				{translations name='field.group'}:
			</td>
			<td class='info_table_value'>
				{foreach $groupsList as $groupElement}<a href="{$groupElement->getUrl()}">{$groupElement->title}</a>{if !$groupElement@last}, {/if}{/foreach}
			</td>
		</tr>
	{/if}
	{if $element->realName}
		<tr>
			<td class='info_table_label'>
				{translations name='field.realname'}:
			</td>
			<td class='info_table_value'>
				{$element->realName}
			</td>
		</tr>
	{/if}
	{if $element->getCityElement() || $element->getCountryElement()}
		<tr>
			<td class='info_table_label'>
				{translations name='field.livinglocation'}:
			</td>
			<td class='info_table_value'>
				{if $city = $element->getCityElement()}<a href="{$city->getUrl('author')}">{$city->title}</a>, {/if}
				{if $country = $element->getCountryElement()}
					<a href="{$country->getUrl('author')}">{$country->title}</a>
				{/if}
			</td>
		</tr>
	{/if}
	{if $aliasElements = $element->getAliasElements()}
		<tr>
			<td class='info_table_label'>
				{translations name='field.othernicknames'}:
			</td>
			<td class='info_table_value'>
				{foreach $aliasElements as $aliasElement}
					<a href="{$aliasElement->getUrl()}">{$aliasElement->title}</a>{if !$aliasElement@last}, {/if}
				{/foreach}
			</td>
		</tr>
	{/if}
	{include file=$theme->template('component.links.tpl')}
	{if $element->displayInMusic}
		<tr>
			<td class='info_table_label'>
				{translations name='author.chiptype'}:
			</td>
			<td class='info_table_value'>
				{translations name="zxmusic.chiptype_{$element->getChipType()}"}
			</td>
		</tr>
		<tr>
			<td class='info_table_label'>
				{translations name='author.channelstype'}:
			</td>
			<td class='info_table_value'>
				{translations name="zxmusic.channelstype_{$element->getChannelsType()}"}
			</td>
		</tr>
		<tr>
			<td class='info_table_label'>
				{translations name='author.frequency'}:
			</td>
			<td class='info_table_value'>
				{translations name="zxmusic.frequency_{$element->getFrequency()}"}
			</td>
		</tr>
		<tr>
			<td class='info_table_label'>
				{translations name='author.intfrequency'}:
			</td>
			<td class='info_table_value'>
				{translations name="zxmusic.intFrequency_{$element->getIntFrequency()|replace:".":""}"}
			</td>
		</tr>
	{/if}
	{if $element->displayInGraphics}
	<tr>
		<td class='info_table_label'>
			{translations name='author.palette'}:
		</td>
		<td class='info_table_value'>
			{translations name="zxpicture.palette_{$element->getPalette()}"}
		</td>
	</tr>
	{/if}
	{if $element->displayInMusic}
		<tr>
			<td class='info_table_label'>
				{translations name='author.rating_music'}:
			</td>
			<td class='info_table_value'>
				{$element->musicRating}
			</td>
		</tr>
	{/if}
	{if $element->displayInGraphics}
		<tr>
			<td class='info_table_label'>
				{translations name='author.rating_graphics'}:
			</td>
			<td class='info_table_value'>
				{$element->graphicsRating}
			</td>
		</tr>
	{/if}
	{$userElement = $element->getUserElement()}
	{if $userElement}
		<tr>
			<td class='info_table_label'>
				{translations name='author.connecteduser'}:
			</td>
			<td class='info_table_value'>
				{$userElement->userName}
			</td>
		</tr>
	{/if}
</table>
