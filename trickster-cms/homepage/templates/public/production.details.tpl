{if $h1 = $element->getH1()}
	{capture assign="moduleTitle"}
		{$h1}
	{/capture}
{elseif $element->title}
	{capture assign='moduleTitle'}
		{$element->title}
	{/capture}
{/if}

{capture assign="moduleContent"}
	<div class="production_details_head">
		{if $element->originalName != ""}
			{include file=$theme->template('component.elementimage.tpl') type='productionDetailsImage' class='production_details_image'}
		{/if}

		<div class='production_details_content html_content'>
			{$element->content}
		</div>
	</div>
	{if $element->feedbackURL}
		<div class="production_details_controls">
			{if $element->originalName2 != ''}
				<a href="{$controller->baseURL}file/id:{$element->file}/filename:{$element->originalName2}" class="button production_details_file">
					<span class='button_text'>{translations name='production.details_pdf'}</span>
				</a>
			{/if}
			<a href="{$element->feedbackURL}" class="button production_details_askmore">
				<span class='button_text'>{translations name='production.details_askmore'}</span>
			</a>
		</div>
	{/if}

	{if count($element->galleriesList)}
		<div class="production_details_galleries">
			{foreach from=$element->galleriesList item=gallery}{include file=$theme->template($gallery->getTemplate()) element=$gallery}{/foreach}
		</div>
	{/if}

{/capture}

{assign moduleClass "production_details_block"}
{assign moduleContentClass "production_details_content_block"}
{assign moduleTitleClass "production_details_heading"}

{include file=$theme->template("component.contentmodule.tpl")}

