{if $authorElement = $element->getAuthorElement()}
	<table class='author_details_info info_table'>
		{if $authorElement->title != ''}
			<tr>
				<td class='info_table_label'>
					{translations name='authoralias.author'}:
				</td>
				<td class='info_table_value'>
					<a href="{$authorElement->getUrl()}">{$authorElement->title}</a>
				</td>
			</tr>
		{/if}
		{if $authorElement->realName != ''}
			<tr>
				<td class='info_table_label'>
					{translations name='authoralias.realname'}:
				</td>
				<td class='info_table_value'>
					{$authorElement->realName}
				</td>
			</tr>
		{/if}
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
		{include file=$theme->template('component.links.tpl')}
	</table>
{/if}