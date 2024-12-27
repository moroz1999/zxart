{$articles = $element->getPressMentions()}
{if $articles !== []}
    <div class="mentions_block">
        <h3 class="mentions_heading">{translations name='press.mentions'}</h3>
        <div class="mentions_articles">
        {foreach $articles as $article}
            {$pressElement = $article->getParent()}
            <div class="mentions_article">
                {if $pressElement}<a href="{$pressElement->getUrl()}">{$pressElement->getTitle()}</a>{if $pressElement->year} ({$pressElement->year}){/if} / {/if}<a href="{$article->getUrl()}">{$article->getTitle()}</a>
                {if $article->introduction}<div class="mentions_article_description">{$article->introduction}</div>{/if}
            </div>
        {/foreach}
        </div>
    </div>
{/if}