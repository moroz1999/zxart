{if $user = $element->getUser()}
{if $user->userName === 'anonymous'}
   {$element->author}
{elseif $url=$user->getUrl()}
    <a href="{$url}">{$user->userName}</a>
{else}
   {$user->userName}
{/if} {$element->dateCreated} <br/>
{/if}
{if isset($displayTarget) && $displayTarget}{if $initialTarget = $element->getInitialTarget()}<a href="{$initialTarget->getUrl()}">{$initialTarget->getHumanReadableName()}</a><br/>{/if}{/if}
<br/>
{$element->content}
<br><br>{include file=$theme->template("component.hr.tpl") symbol="."}<br><br><br>
{if !isset($displaySubComments) || $displaySubComments}
    {if $commentsList = $element->getCommentsList()}
        {foreach from=$commentsList item=comment}
            {include file=$theme->template("comment.full.tpl") element=$comment}
        {/foreach}
    {/if}
{/if}