{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{if $element->content != ''}
		<div class='selectedgalleries_description html_content'>
			{$element->content}
		</div>
	{/if}
	{if count($element->galleriesList)}
		<div class='selectedgalleries_galleries'>
			{foreach from=$element->galleriesList item=gallery}
				{include file=$theme->template('gallery.short.tpl') element=$gallery}
			{/foreach}
		</div>
	{/if}
{/capture}

{assign moduleClass "selected_galleries_block"}
{assign moduleTitleClass "selectedgalleries_heading"}
{assign moduleContentClass "selectedgalleries_content"}

{include file=$theme->template("component.contentmodule.tpl")}