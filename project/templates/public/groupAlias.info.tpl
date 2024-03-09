{if $groupElement = $element->getGroupElement()}
	<div class="group_details_photo">
		{if $groupElement->originalName != ""}
			<img loading="lazy" class="group_details_photo_image" src='{$controller->baseURL}image/type:groupPhoto/id:{$groupElement->image}/filename:{$groupElement->originalName}' alt="{$groupElement->title}" />
		{else}
			<img loading="lazy" class="group_details_photo_image" src='{$theme->getImageUrl('group.svg')}' alt="" />
		{/if}
	</div>
	<table class='group_details_info info_table'>
		{if $groupElement->title != ''}
			<tr>
				<td class='info_table_label'>
					{translations name='groupalias.group'}:
				</td>
				<td class='info_table_value'>
					<a href="{$groupElement->getUrl()}">{$groupElement->title}</a>
				</td>
			</tr>
		{/if}
		{include file=$theme->template('component.links.tpl')}
	</table>
{else}
    <table class='group_details_info info_table'>
        {include file=$theme->template('component.links.tpl')}
    </table>

{/if}