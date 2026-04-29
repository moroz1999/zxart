{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div class='widget_content html_content'>
		<div class="widget_image_block">
			{if $element->originalName != ''}
				<img class='widget_image lazy_image' src="{$theme->getImageUrl("lazy.png")}" data-lazysrc="{$controller->baseURL}image/type:widgetImage/id:{$element->image}/filename:{$element->originalName}"  alt='{$element->title}'/>
			{/if}
		</div>
		{$element->content}
	</div>
	<div>
		{$element->code}
	</div>
{/capture}


{assign moduleClass "widget_block"}
{assign moduleTitleClass "widget_heading"}

{include file=$theme->template("component.contentmodule.tpl")}
