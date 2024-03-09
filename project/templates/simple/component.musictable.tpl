{if !isset($showPartyPlace)}{assign var="showPartyPlace" value=false}{/if}{if !isset($showAuthors)}{assign var="showAuthors" value=true}{/if}{if !isset($showYear)}{assign var="showYear" value=true}{/if}
{if isset($pager)}{include file=$theme->template("pager.tpl") pager=$pager}{/if}
{if !isset($number)}{$number=1}{/if}{foreach from=$musicList item=music name=musicList}{include file=$theme->template("zxMusic.table.tpl") element=$music odd=0 number=$number}{$number=$number+1}{/foreach}
{if isset($pager)}{include file=$theme->template("pager.tpl") pager=$pager}{/if}
