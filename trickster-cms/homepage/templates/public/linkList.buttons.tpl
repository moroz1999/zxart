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
				{foreach $element->linkItems as $linkItem}
					{include file=$theme->template('linkListItem.button.tpl') element=$linkItem}
				{/foreach}
			{/stripdomspaces}
		</div>
	{/if}
{/capture}
{assign moduleClass "linklist linklist_{$element->getCurrentLayout()}"}
{assign moduleTitleClass "linklist_title"}
{include file=$theme->template("component.contentmodule.tpl")}