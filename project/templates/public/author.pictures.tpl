{if ($element->getYearsWorks('authorPicture'))}
	<div class="author_details_section">
		<h1>{translations name="author.graphics"}</h1>
		<a class="author_details_save button" href="{$element->getSaveUrl('zxPicture')}">{translations name='author.save'}</a>
		<div class='author_pictures gallery_pictures' id="gallery_{$element->id}">
			{foreach from=$element->getYearsWorks('authorPicture') key=year item=pictures}
				<div class='author_details_year'>
					<h2 class="author_details_year_title">{if $year=='0'}{translations name='label.unknownyear'}{else}{$year}{/if}</h2>
					{include file=$theme->template('component.pictureslist.tpl') pictures=$pictures}
				</div>
			{/foreach}
		</div>
	</div>
{/if}