{capture assign="moduleContent"}
	<a {if !empty($element->link)} href="{$element->link}" {/if}class='linklist_item_thumbnail_link linklist_item_overlay_container' title="{$element->linkText|default:$element->title}" {if !$element->isLinkInternal()} target="_blank"{/if}>
		{if $element->originalName}
			<span class="linklist_item_thumbnail_image_wrap">
				{include file=$theme->template('component.elementimage.tpl') type='linklistItemThumbnail' class='linklist_item_thumbnail_image' lazy=true}
			</span>
		{/if}

		{if $element->content}
			<span class="linklist_item_thumbnail_overlay linklist_item_overlay">
				{if $element->title}
					<span class='linklist_item_thumbnail_title linklist_item_title'>{$element->title}</span>
				{/if}
				{if $element->content}
					<span class="linklist_item_thumbnail_content html_content">
						{$element->content}
					</span>
				{/if}
			</span>
		{elseif $element->title}
			<span class='linklist_item_thumbnail_title'>{$element->title}</span>
		{/if}
	</a>
{/capture}
{assign moduleClass "linklist_item_thumbnail"}
{assign moduleTitleClass ""}
{assign moduleTitle ""}
{include file=$theme->template("component.subcontentmodule_square.tpl")}