{capture assign="moduleContent"}
	{if $element->title}
		{capture assign="moduleTitle"}
			{$element->title}
		{/capture}
	{/if}

	{if $element->content}
		<div class='linklist_content html_content'>
			{$element->content}
		</div>
	{/if}

	{if $element->originalName}
		<span class="linklist_list_image_wrap">
			{include file=$theme->template('component.elementimage.tpl') type='linklist' class='linklist_list_image' lazy=true}
		</span>
	{/if}

	{if $element->linkItems}
		<div class='linklist_items'>
			{stripdomspaces}
				{foreach $element->linkItems as $linkItem}
					<a class="linklist_list_item" href="{$linkItem->link}">
						{$linkItem->title}
					</a>
				{/foreach}
			{/stripdomspaces}
		</div>
	{/if}
{/capture}
{assign moduleClass "linklist linklist_{$element->getCurrentLayout()}"}
{assign moduleTitleClass "linklist_title"}
{include file=$theme->template("component.contentmodule.tpl")}