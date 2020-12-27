{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{include file=$theme->template("pager.tpl") pager=$element->getPager()}
<br><br>
{foreach from=$element->getCommentsList() item=comment}
	{include file=$theme->template("comment.full.tpl") element=$comment displaySubComments=false}
{/foreach}
{include file=$theme->template("pager.tpl") pager=$element->getPager()}