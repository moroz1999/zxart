{if isset($pager)}{include file=$theme->template("pager.tpl") pager=$pager}{/if}
{if !isset($number)}{$number=1}{/if}
{foreach from=$authorsList item=author name=authorsList}{include file=$theme->template($author->getTemplate('table')) element=$author}{$number=$number+1}{/foreach}
{if isset($pager)}{include file=$theme->template("pager.tpl") pager=$pager}{/if}
