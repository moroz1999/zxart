{if ($element->getYearsWorks('authorMusic'))}
	<div class="author_details_section">
		<h1>{translations name="author.music"}</h1>
		<a class="author_details_save button" href="{$element->getSaveUrl('zxMusic')}">{translations name='author.save_music'}</a>
		<div class='author_musics gallery_musics'>
			{foreach from=$element->getYearsWorks('authorMusic') key=year item=tunes}
				<div class='author_details_year'>
					<h2 class="author_details_year_title">{if $year=='0'}{translations name='label.unknownyear'}{else}{$year}{/if}</h2>
					{include file=$theme->template("component.musictable.tpl") musicList=$tunes element=$element showAuthors=false showYear=false musicListId="author_music_{$element->id}_{$year}"}
				</div>
			{/foreach}
		</div>

	</div>
{/if}
