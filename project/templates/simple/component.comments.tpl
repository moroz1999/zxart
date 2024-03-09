{if $element->areCommentsAllowed()}
	{if $commentsList = $element->getCommentsList()}
		{foreach from=$commentsList item=comment}
			{include file=$theme->template("comment.full.tpl") element=$comment displayTarget=false}
		{/foreach}
	{/if}
{/if}