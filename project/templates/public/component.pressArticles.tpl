<ul class="pressarticles_list">
    {foreach $articles as $article}
        <li class="pressarticles_list_item">
            <span class="pressarticles_list_top">
                <span class="pressarticles_list_left">
                <a href="{$article->getUrl()}">{$article->title}</a>
                {if $article->authors} - {foreach $article->authors as $author}<a href="{$author->getUrl()}">{$author->getTitle()}</a>{if !$author@last}, {/if}{/foreach}{/if}
                </span>
                {if isset($currentElementPrivileges.showAiForm) && $currentElementPrivileges.showAiForm==1}
                <span class="pressarticles_list_right">
                    {if $article->getQueueStatus(ZxArt\Queue\QueueType::AI_PRESS_FIX)}<span class="pressarticles_list_status">{translations name='pressArticle.fix'}</span>{/if}
                    {if $article->getQueueStatus(ZxArt\Queue\QueueType::AI_PRESS_TRANSLATE)}<span class="pressarticles_list_status">{translations name='pressArticle.translate'}</span>{/if}
                    {if $article->getQueueStatus(ZxArt\Queue\QueueType::AI_PRESS_PARSE)}<span class="pressarticles_list_status">{translations name='pressArticle.parse'}</span>{/if}
                    {if $article->getQueueStatus(ZxArt\Queue\QueueType::AI_PRESS_SEO)}<span class="pressarticles_list_status">{translations name='pressArticle.seo'}</span>{/if}
                </span>
                {/if}
            </span>
            {if $article->introduction}<span class="pressarticles_list_description">{$article->introduction}</span>{/if}
        </li>
    {/foreach}
</ul>