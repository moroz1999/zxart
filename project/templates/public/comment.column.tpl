{assign "user" $element->getUserElement()}
{if $user}
<div class='comment comment_short'>{$initialTarget = $element->getInitialTarget()}
	<a href="{if $initialTarget}{$initialTarget->getUrl()}">{if $user}{include file=$theme->template("component.username.tpl") userClass='comment_author' userName=$user->userName userType=$user->getBadgeTypesString()}: {/if}<span class='comment_content'>{$element->content}</span></a>{/if}
</div>
{/if}
