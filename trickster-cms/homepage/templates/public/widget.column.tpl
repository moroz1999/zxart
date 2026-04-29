{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{if $element->content || $element->originalName}
		<div class='column_widget_content html_content'>
			<div class="column_widget_image_block">
				{if $element->originalName != ''}
					<img class='column_widget_image' src='{$controller->baseURL}image/type:widgetImage/id:{$element->image}/filename:{$element->originalName}' alt='{$element->title}'/>
				{/if}
			</div>
			{if $element->content}{$element->content}{/if}
		</div>
	{/if}
	<div class="column_widget_code">
		{$element->code}
	</div>
{/capture}


{assign moduleClass "column_widget_block"}
{assign moduleTitleClass "column_widget_heading"}
{assign moduleContentClass "column_widget_content"}

{include file=$theme->template("component.columnmodule.tpl")}