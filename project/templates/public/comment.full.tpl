{capture assign="moduleContent"}
	{assign "user" $element->getUserElement()}
	{if $user}
		<div class='comment_info'>
			{if $user->userName === 'anonymous'}
				<span>{$element->author}</span>
			{elseif $url=$user->getUrl()}
				{include file=$theme->template("component.username.tpl") userUrl=$url userClass='comment_author' userName=$user->userName userType=$user->getBadgeTypesString()}
			{else}
				<span class='comment_author'>{$user->userName}</span>
			{/if}
			<span class='comment_date'>{$element->dateCreated}</span>
			{if !$element->isVotingDenied()}{include file=$theme->template('comment.votecontrols.tpl') element=$element}{/if}
		</div>
	{/if}
	<script>
		if (!window.commentsList) {
			window.commentsList = [];
		}
		window.commentsList.push({$element->getJsonInfo()});
	</script>
	{if isset($displayTarget) && $displayTarget}
		{if $initialTarget = $element->getInitialTarget()}
			<a class="comment_content_link" href="{$initialTarget->getUrl()}">{$initialTarget->getTitle()}</a>
		{/if}
	{/if}
	<div class='comment_content'>
		{$element->getDecoratedContent()}
	</div>
	{if isset($privileges.comment.publicReceive) && $privileges.comment.publicReceive}
		{if $element->areCommentsAllowed()}
			{if $commentForm = $element->getCommentForm()}
				<a class="comment_response_button">{translations name='comment.respond'}</a>
				{include file=$theme->template($commentForm->getTemplate()) element=$commentForm registeredOnly=$element->areCommentsRegisteredOnly()}
			{/if}
		{/if}
	{/if}
{/capture}
{assign "moduleTitle" ""}
{assign "moduleClass" "comment"}
{assign "moduleAttributes" "id='vote_id_{$element->id}' {if $element->areCommentsRegisteredOnly()}data-registered-only='true'{/if}"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}
{if !isset($displaySubComments) || $displaySubComments}
	{if $commentsList = $element->getCommentsList()}
		<div class="comments_comments comments_list">
			{foreach from=$commentsList item=comment}
				{include file=$theme->template("comment.full.tpl") element=$comment}
			{/foreach}
		</div>
	{/if}
{/if}