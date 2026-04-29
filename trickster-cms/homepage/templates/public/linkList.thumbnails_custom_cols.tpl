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
		<div class='linklist_items linklist_items_{$element->getCurrentLayout()}'>
			{stripdomspaces}
				{foreach $element->linkItems as $linkItem}
					{include file=$theme->template('linkListItem.thumbnails_custom_cols.tpl') colsOnRow=$element->cols element=$linkItem}
				{/foreach}
			{/stripdomspaces}
		</div>
	{/if}
{/capture}
{assign moduleClass "linklist linklist_{$element->getCurrentLayout()}"}
{assign moduleTitleClass "linklist_title"}
{*{assign contentOnly true}*}
{assign moduleContentClass "linklist linklist_{$element->getCurrentLayout()}"}
{include file=$theme->template("component.contentmodule.tpl")}
{*{include file=$theme->template("component.subcontentmodule_set_cols.tpl") moduleTitle=false}*}