{if $children = $element->getChildrenList()}
	{if $element->title}
		{capture assign="moduleTitle"}
			{$element->title}
		{/capture}
	{/if}
	{capture assign="moduleContent"}
		<div class="tabs">
			{if count($children)>1}
			<div class="tabs_buttons">{stripdomspaces}
				{foreach $children as $child}
					<div class="tab_button">
						<div class="tab_button_inner">
							{$child->title}
						</div>
					</div>
				{/foreach}
				{/stripdomspaces}
			</div>
			{/if}
			<div class="tabs_items">
				{foreach $children as $child}
					<div class="tabs_item">
						{include file=$theme->template($child->getTemplate()) element=$child contentOnly=true}
					</div>
				{/foreach}
			</div>
		</div>
	{/capture}
	{assign moduleClass "tabswidget"}
	{assign moduleTitleClass "tabswidget_title"}
	{assign moduleContentClass "tabswidget_content"}

	{include file=$theme->template("component.contentmodule.tpl")}
{/if}