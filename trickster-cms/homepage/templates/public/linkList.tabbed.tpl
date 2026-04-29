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

	{if count($element->linkItems)}
		<div class='linklist_items'>
			{stripdomspaces}
				<div class="tabs linklist_tabbed_inner">
					{if count($element->linkItems)>1}
					<div class="linklist_tabbed_buttons tabs_buttons">
						{foreach $element->linkItems as $linkItem}
							<div class="tab_button linklist_tab_button">
								<div class="tab_button_inner">
									{$linkItem->title}
								</div>
							</div>
						{/foreach}
					</div>
					{/if}
					<div class="linklist_tabbed_tabs_contents tabs_items">
						{foreach $element->linkItems as $linkItem}
							<div class="linklist_tabbed_item tabs_item">
								{if $linkItem->originalName}
									<div class="linklist_tabbed_item_image_wrap">
										{include file=$theme->template('component.elementimage.tpl') type='linklistItemTabbed' class='linklist_tabbed_item_image' lazy=true}
									</div>
								{/if}
								<div class="linklist_tabbed_item_description html_content">
									{$linkItem->content}
								</div>
								<a class="linklist_tabbed_item_link button" href="{$linkItem->link}"{if !$linkItem->isLinkInternal()} target="_blank"{/if}>
									{translations name='linklist.readmore'}
									<span class="linklist_tabbed_item_link_arrow"></span>
								</a>
							</div>
						{/foreach}
					</div>
				</div>
			{/stripdomspaces}
		</div>
	{/if}
{/capture}

{assign moduleClass "linklist linklist_{$element->getCurrentLayout()}"}
{assign moduleTitleClass "linklist_title"}
{include file=$theme->template("component.contentmodule.tpl")}