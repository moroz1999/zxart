{assign moduleTitle $element->title}
{if $element->originalName}
	{capture assign="moduleSideContent"}
		{include file=$theme->template('component.elementimage.tpl') type='linklistItemDetailed' class='linklist_item_detailed_image' lazy=true}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<a href="{$element->link}" class='linklist_item_detailed_link' title="{$element->linkText|default:$element->title}" {if !$element->isLinkInternal()} target="_blank"{/if}>
		{if $element->content}
			<span class="linklist_item_detailed_content html_content">
				{$element->content}
			</span>
		{/if}
	</a>
{/capture}

{if $element->linkText}
	{capture assign="moduleControls"}
		<a href="{$element->link}" class="button linklist_item_detailed_button"><span class="button_text">{$element->linkText}</span></a>
	{/capture}
{/if}

{assign moduleClass "linklist_item_detailed"}
{assign moduleTitleClass "linklist_item_detailed_title"}
{assign moduleAttributes ""}
{assign moduleSideContentClass "linklist_item_detailed_image_wrap"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}
