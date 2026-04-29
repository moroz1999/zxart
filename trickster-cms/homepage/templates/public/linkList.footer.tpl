<div class="linklist linklist_footer">
	{if $element->title}
		<div class="linklist_title">{$element->title}</div>
	{/if}

	{if $element->content}
		<div class='linklist_content html_content'>
			{$element->content}
		</div>
	{/if}

	{if count($element->linkItems)}
		<div class='linklist_items'>
			{stripdomspaces}
			{foreach $element->linkItems as $linkItem}
				{include file=$theme->template('linkListItem.footer.tpl') element=$linkItem}
			{/foreach}
			{/stripdomspaces}
		</div>
	{/if}
</div>