<ul class="pressarticles_list">
    {foreach $articles as $article}
        <li class="pressarticles_list_item">
            <a href="{$article->getUrl()}">{$article->title}</a>
            {if $article->authors} - {foreach $article->authors as $author}<a href="{$author->getUrl()}">{$author->getTitle()}</a>{if !$author@last}, {/if}{/foreach}{/if}
            {if $article->introduction}<div class="pressarticles_list_description">{$article->introduction}</div>{/if}
        </li>
    {/foreach}
</ul>