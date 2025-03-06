{assign "user" $element->getUser()}
{if $user}
<div class='comment comment_short'>
	<a href="{$element->getInitialTarget()->getUrl()}">{if $user}{include file=$theme->template("component.username.tpl") userClass='comment_author' userName=$user->userName userType=$user->getBadgeTypesString()}: {/if}<span class='comment_content'>{$element->content}</span></a>
</div>
{/if}
