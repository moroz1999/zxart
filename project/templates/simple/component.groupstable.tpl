{if isset($pager)}{include file=$theme->template("pager.tpl") pager=$pager}{/if}
{if !isset($number)}{$number=1}{/if}
{foreach from=$groupsList item=group name=groupsList}
	{include file=$theme->template($group->getTemplate('table')) element=$group}
	{$number=$number+1}
{/foreach}
{if isset($pager)}{include file=$theme->template("pager.tpl") pager=$pager}{/if}