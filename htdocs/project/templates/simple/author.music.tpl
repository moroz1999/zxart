{if ($element->getYearsWorks('authorMusic'))}
	{include file=$theme->template('component.heading.tpl') value={translations name="author.music"}}
	{foreach from=$element->getYearsWorks('authorMusic') key=year item=tunes}
			{if $year=='0'}{translations name='label.unknownyear'}{else}{$year}{/if}<br><br>
			{include file=$theme->template("component.musictable.tpl") musicList=$tunes element=$element showAuthors=false showYear=false}
	{/foreach}
{/if}