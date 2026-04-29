{capture assign="moduleContent"}
	<a {if !empty($element->link)} href="{$element->link}" {/if}class='linklist_item_custom_cols_link' title="{$element->linkText|default:$element->title}" {if !$element->isLinkInternal()} target="_blank"{/if}>
		{if $element->content}
			{if $element->title}
				<span class='linklist_item_thumbnail_title_cols linklist_item_title'>{$element->title}</span>
			{/if}
			{if $element->content}
				<span class="linklist_item_thumbnail_content html_content">
					{$element->content}
				</span>
			{/if}
		{elseif $element->title}
			<span class='linklist_item_thumbnail_title_cols'>{$element->title}</span>
		{/if}
	</a>
{/capture}
{capture assign="moduleImageBlock"}
	<a {if !empty($element->link)} href="{$element->link}" {/if}class='linklist_item_custom_cols_link' title="{$element->linkText|default:$element->title}" {if !$element->isLinkInternal()} target="_blank"{/if}>
		{if $element->originalName}
			<span class="linklist_item_thumbnail_image_wrap">
				{include file=$theme->template('component.elementimage.tpl') type='linklistItemThumbnailLong' class='linklist_item_thumbnail_image' lazy=true}
			</span>
		{/if}
	</a>
{/capture}
{assign moduleClass "linklist_item_thumbnail_custom_cols"}
{assign moduleTitleClass ""}
{assign moduleTitle ""}
{assign moduleTag "div"}
{include file=$theme->template("component.subcontentmodule_set_cols.tpl") moduleTitle=false colsOnRow={$colsOnRow}}
{*{include file=$theme->template("component.subcontentmodule_square.tpl")}*}