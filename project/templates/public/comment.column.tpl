{assign "user" $element->getUser()}
{if $user}
<div class='comment comment_short'>
	<a href="{$element->getInitialTarget()->getUrl()}">{if $user}<span class='comment_author'>{$user->userName}:</span> {/if}<span class='comment_content'>{$element->content}</span></a>
</div>
{/if}
