<a href="{$element->link}" class="linklist_item_button button" title="{$element->linkText|default:$element->title}" {if !$element->isLinkInternal()} target="_blank"{/if}>
	<span class="linklist_item_button_contents">
		<span class="linklist_item_button_title">
			{$element->title}
		</span>
		{if $element->image}
			<img class="linklist_item_button_image" src='{$controller->baseURL}image/type:linklistItemButton/id:{$element->image}/filename:{$element->originalName}' alt='{$element->title}'/>
		{/if}
	</span>
</a>