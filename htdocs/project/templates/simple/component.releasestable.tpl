{if isset($pager)}{include file=$theme->template("pager.tpl") pager=$pager}{/if}
{if !isset($number)}{$number=1}{/if}
{foreach from=$releasesList item=release name=releasesList}
    {include file=$theme->template("zxRelease.table.tpl") element=$release number=$number}
    {$number=$number+1}
{/foreach}
{if isset($pager)}{include file=$theme->template("pager.tpl") pager=$pager}{/if}