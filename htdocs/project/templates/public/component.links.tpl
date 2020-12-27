{if $links = $element->getLinksInfo()}
	<tr>
		<td class='info_table_label'>
			{translations name='links.links'}:
		</td>
		<td class='info_table_value'>
			{foreach $links as $linkInfo}
				<a target="_blank" class="import_link" href='{$linkInfo['url']}'><img class="import_link_image" src="{$theme->getImageUrl($linkInfo.image)}">{$linkInfo.name}</a>
			{/foreach}
		</td>
	</tr>
{/if}
