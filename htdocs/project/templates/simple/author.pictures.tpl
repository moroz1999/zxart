{if ($element->getYearsWorks('authorPicture'))}
	{include file=$theme->template('component.heading.tpl') value={translations name="author.graphics"}}
    {foreach from=$element->getYearsWorks('authorPicture') key=year item=pictures}
        {if $year=='0'}{translations name='label.unknownyear'}{else}{$year}{/if}<br><br>
        {foreach from=$pictures item=picture}{include file=$theme->template("zxPicture.short.tpl") element=$picture}{/foreach}
    {/foreach}
{/if}