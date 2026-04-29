{if $element->linkItems}
	<div class="linklist_buttons linklist_header">
		<div class='linklist_items linklist_items_buttons'>
			{stripdomspaces}
			{foreach $element->linkItems as $linkItem}
				{include file=$theme->template('linkListItem.button.tpl') element=$linkItem}
			{/foreach}
			{/stripdomspaces}
		</div>
	</div>
{/if}